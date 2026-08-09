<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Symfony\Component\Process\Exception\RuntimeException as ProcessRuntimeException;
use Symfony\Component\Process\Process;

/**
 * Creates and manages gzipped mysqldump archives of the application database.
 *
 * Archives live in storage/app/backups — outside the web root, so the only way
 * to reach one is through the authenticated download route.
 *
 * The shared host disables exec/shell_exec/system/popen and even
 * escapeshellarg, so the dump runs through Symfony's Process (proc_open plus
 * its own argument escaping) with an argument array — never a shell string.
 * `--result-file` keeps the output off the shell as well.
 */
class DatabaseBackupService
{
    /** Leftovers older than this are assumed to be from a killed request. */
    private const STALE_FILE_SECONDS = 3600;

    /** The trailing counter disambiguates two backups made within one second. */
    private const NAME_PATTERN = '/^backup-[A-Za-z0-9_\-]+-\d{4}-\d{2}-\d{2}_\d{6}(-\d+)?\.sql\.gz$/';

    private const LOCK_NAME = 'database-backup';

    /** Head room so the lock always outlives the dump it is guarding. */
    private const LOCK_MARGIN_SECONDS = 60;

    private ?string $storageDirectory = null;

    private ?string $temporaryDirectory = null;

    /**
     * Run a fresh dump, store it, and drop the oldest archives over the limit.
     *
     * @return array{name: string, size: int, created_at: string}
     */
    public function create(): array
    {
        // Two dumps at once would fight over the same temporary file, and the
        // first to finish would delete it from under the other.
        // Derived from the dump timeout: a bigger database gets a longer timeout,
        // and a lock that expired mid-dump would drop the guarantee silently.
        $lock = Cache::lock(self::LOCK_NAME, $this->dumpTimeout() + self::LOCK_MARGIN_SECONDS);

        if (! $lock->get()) {
            throw new RuntimeException('A backup is already running. Please try again in a moment.', 409);
        }

        try {
            return $this->runBackup();
        } finally {
            $lock->release();
        }
    }

    /** @return array{name: string, size: int, created_at: string} */
    private function runBackup(): array
    {
        $config = $this->connectionConfig();
        $workDir = $this->temporaryDirectory();

        $name = $this->uniqueName($config['database']);
        $sqlPath = $workDir . '/' . $name . '.sql';
        $gzPath = $this->storageDirectory() . '/' . $name;
        $credentialsPath = $workDir . '/.dump-' . bin2hex(random_bytes(8)) . '.cnf';

        $this->writeCredentialsFile($credentialsPath, $config);

        try {
            $this->runDump($credentialsPath, $config['database'], $sqlPath);
            $this->assertDumpIsComplete($sqlPath);
            $this->compress($sqlPath, $gzPath);
            $this->assertArchiveIsIntact($gzPath, (int) filesize($sqlPath));
        } catch (\Throwable $e) {
            // Never leave a half-written archive in the list: a backup that
            // only fails at restore time is worse than no backup at all.
            @unlink($gzPath);

            throw $e;
        } finally {
            @unlink($credentialsPath);
            @unlink($sqlPath);
        }

        $this->rotate();

        return $this->describe($name);
    }

    /** Second-resolution names can repeat, so fall back to a counter. */
    private function uniqueName(string $database): string
    {
        // The generated name has to satisfy NAME_PATTERN, otherwise the archive
        // would be written but then be invisible to the list and undownloadable.
        $slug = preg_replace('/[^A-Za-z0-9_\-]/', '_', $database);
        $base = 'backup-' . ($slug === '' ? 'database' : $slug) . '-' . Carbon::now()->format('Y-m-d_His');
        $dir = $this->storageDirectory();

        for ($attempt = 1; $attempt <= 99; $attempt++) {
            $name = $base . ($attempt === 1 ? '' : '-' . $attempt) . '.sql.gz';

            if (! file_exists($dir . '/' . $name)) {
                return $name;
            }
        }

        throw new RuntimeException('Could not allocate a backup file name.');
    }

    /**
     * Stored archives, newest first.
     *
     * @return array<int, array{name: string, size: int, created_at: string}>
     */
    public function all(): array
    {
        return array_map(fn (string $name) => $this->describe($name), $this->sortedNames());
    }

    public function path(string $name): string
    {
        $path = $this->storageDirectory() . '/' . $this->validateName($name);

        if (! is_file($path)) {
            throw new RuntimeException('Backup not found.', 404);
        }

        return $path;
    }

    public function delete(string $name): void
    {
        if (! @unlink($this->path($name))) {
            throw new RuntimeException('Could not delete the backup file.');
        }
    }

    public function keepLimit(): int
    {
        return max(1, (int) config('database.dump.keep'));
    }

    private function dumpTimeout(): int
    {
        return max(1, (int) config('database.dump.timeout'));
    }

    /** The counter in a "…-2.sql.gz" name; an unsuffixed name is the first one. */
    private function nameCounter(string $name): int
    {
        return preg_match('/-(\d+)\.sql\.gz$/', $name, $matches) ? (int) $matches[1] : 1;
    }

    /**
     * Reject anything that is not one of our own archive names, so a crafted
     * value can never point the download or delete at another file.
     */
    private function validateName(string $name): string
    {
        $name = basename($name);

        if (! preg_match(self::NAME_PATTERN, $name)) {
            throw new RuntimeException('Invalid backup name.', 422);
        }

        return $name;
    }

    /** Delete the oldest archives once the limit is exceeded. */
    private function rotate(): void
    {
        foreach (array_slice($this->sortedNames(), $this->keepLimit()) as $name) {
            @unlink($this->storageDirectory() . '/' . $name);
        }
    }

    /**
     * Newest first. Sorted by modification time rather than by name, because a
     * "-2" suffix would otherwise sort before the file it followed.
     *
     * @return array<int, string>
     */
    private function sortedNames(): array
    {
        $dir = $this->storageDirectory();
        $names = $this->fileNames();

        usort($names, function (string $a, string $b) use ($dir) {
            $byTime = filemtime($dir . '/' . $b) <=> filemtime($dir . '/' . $a);

            if ($byTime !== 0) {
                return $byTime;
            }

            // Same second — which is exactly when the counter suffix appears.
            // It has to be read as a number: sorting the names as text puts "-2"
            // before "-3" and both before the unsuffixed (earliest) file.
            return $this->nameCounter($b) <=> $this->nameCounter($a);
        });

        return $names;
    }

    /** @return array<int, string> */
    private function fileNames(): array
    {
        $names = [];

        foreach (scandir($this->storageDirectory()) ?: [] as $entry) {
            if (preg_match(self::NAME_PATTERN, $entry)) {
                $names[] = $entry;
            }
        }

        return $names;
    }

    /** @return array{name: string, size: int, created_at: string} */
    private function describe(string $name): array
    {
        $path = $this->storageDirectory() . '/' . $name;

        return [
            'name' => $name,
            'size' => is_file($path) ? (int) filesize($path) : 0,
            'created_at' => Carbon::createFromTimestamp(filemtime($path) ?: time())->toIso8601String(),
        ];
    }

    private function connectionConfig(): array
    {
        $name = config('database.default');
        $config = config("database.connections.{$name}");

        if (($config['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('Database backup only supports MySQL/MariaDB connections.');
        }

        return $config;
    }

    /**
     * Resolved once per instance: listing 25 archives asks for this directory
     * 27 times, and creating plus chmod'ing it on every call would make a
     * plain lookup do filesystem work over and over.
     */
    private function storageDirectory(): string
    {
        return $this->storageDirectory ??= $this->makeDirectory(storage_path('app/backups'));
    }

    private function temporaryDirectory(): string
    {
        if ($this->temporaryDirectory === null) {
            $this->temporaryDirectory = $this->makeDirectory(storage_path('app/backup-tmp'));

            $this->purgeStaleFiles($this->temporaryDirectory);
        }

        return $this->temporaryDirectory;
    }

    private function makeDirectory(string $dir): string
    {
        // storage/ is always inside open_basedir on shared hosting, unlike /tmp.
        if (! is_dir($dir) && ! mkdir($dir, 0750, true) && ! is_dir($dir)) {
            throw new RuntimeException('Could not create the backup directory.');
        }

        // Dumps carry every row in the database, so keep the directory off-limits
        // to other accounts on the shared host regardless of the inherited umask.
        @chmod($dir, 0750);

        return $dir;
    }

    /**
     * A request killed mid-dump (deploy restart, execution timeout, aborted
     * download) never reaches its cleanup, leaving the dump — and the option
     * file holding the database password — on disk. Sweep those on the way in.
     */
    private function purgeStaleFiles(string $dir): void
    {
        $cutoff = time() - self::STALE_FILE_SECONDS;

        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $file = $dir . '/' . $entry;

            if (is_file($file) && filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }

    /**
     * Credentials go in an option file rather than the command line so they
     * never show up in the process list.
     */
    private function writeCredentialsFile(string $path, array $config): void
    {
        $escape = static fn (string $value): string => str_replace(['\\', '"'], ['\\\\', '\\"'], $value);

        $contents = "[client]\n"
            . 'user="' . $escape((string) ($config['username'] ?? '')) . "\"\n"
            . 'password="' . $escape((string) ($config['password'] ?? '')) . "\"\n"
            . 'host="' . $escape((string) ($config['host'] ?? '127.0.0.1')) . "\"\n"
            . 'port=' . (int) ($config['port'] ?? 3306) . "\n";

        // Create the file empty and lock it down *before* the password goes in:
        // the host's umask is 0002, so writing first would leave the credentials
        // world-readable for the moment between the write and the chmod.
        if (file_put_contents($path, '') === false || ! chmod($path, 0600)) {
            throw new RuntimeException('Could not create the temporary credentials file.');
        }

        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException('Could not write the temporary credentials file.');
        }
    }

    private function runDump(string $credentialsPath, string $database, string $sqlPath): void
    {
        $command = [
            config('database.dump.binary'),
            // Must be the first option for the mysql client tools.
            '--defaults-extra-file=' . $credentialsPath,
            '--single-transaction',
            '--quick',
            // Avoids needing the PROCESS privilege, which shared hosting rarely grants.
            '--no-tablespaces',
            '--skip-lock-tables',
            '--add-drop-table',
            // The schema has none of these today, but a backup that silently
            // omits the first trigger someone adds is not a backup.
            '--routines',
            '--triggers',
            '--events',
            '--default-character-set=utf8mb4',
            '--result-file=' . $sqlPath,
            $database,
        ];

        $process = new Process($command);
        $process->setTimeout($this->dumpTimeout());

        try {
            $process->run();
        } catch (ProcessRuntimeException $e) {
            throw new RuntimeException(
                'Could not start mysqldump (' . config('database.dump.binary') . '): ' . $e->getMessage()
            );
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException('mysqldump failed: ' . trim($process->getErrorOutput()));
        }

        if (! is_file($sqlPath) || filesize($sqlPath) === 0) {
            throw new RuntimeException('mysqldump produced an empty file.');
        }
    }

    /**
     * mysqldump closes its output with a "-- Dump completed" comment. If the
     * process was cut short (disk full, killed mid-write) the marker is missing,
     * and the SQL would restore only part of the database.
     */
    private function assertDumpIsComplete(string $sqlPath): void
    {
        $size = (int) filesize($sqlPath);
        $handle = fopen($sqlPath, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Could not read the generated dump.');
        }

        fseek($handle, max(0, $size - 512));
        $tail = (string) fread($handle, 512);
        fclose($handle);

        if (! str_contains($tail, 'Dump completed')) {
            throw new RuntimeException('The dump is incomplete and was discarded. Check the free disk space.');
        }
    }

    private function compress(string $sqlPath, string $gzPath): void
    {
        $source = fopen($sqlPath, 'rb');
        if ($source === false) {
            throw new RuntimeException('Could not read the generated dump.');
        }

        $target = gzopen($gzPath, 'wb9');
        if ($target === false) {
            fclose($source);
            throw new RuntimeException('Could not create the compressed backup.');
        }

        $failure = null;

        try {
            while (! feof($source)) {
                $chunk = fread($source, 1024 * 1024);

                if ($chunk === false) {
                    throw new RuntimeException('Failed while reading the dump.');
                }

                if ($chunk === '') {
                    continue;
                }

                // A full disk or an exceeded quota makes gzwrite return a short
                // count instead of raising anything — check it, or the archive
                // ends up truncated while the request still reports success.
                if (gzwrite($target, $chunk) !== strlen($chunk)) {
                    throw new RuntimeException('Failed while writing the backup. Check the free disk space.');
                }
            }
        } catch (\Throwable $e) {
            $failure = $e;
        }

        fclose($source);
        $closed = gzclose($target);

        // When the disk fills, gzwrite fails first and gzclose fails right after
        // it. Throwing from a finally would replace the message that explains
        // what happened with a generic one, so the original wins here.
        if ($failure !== null) {
            throw $failure;
        }

        if (! $closed) {
            throw new RuntimeException('Failed while finalising the backup file.');
        }
    }

    /**
     * Read the finished archive back. This walks the gzip CRC and length
     * trailer, so a truncated or corrupted file is caught here rather than on
     * the day someone needs to restore it.
     */
    private function assertArchiveIsIntact(string $gzPath, int $expectedBytes): void
    {
        $handle = gzopen($gzPath, 'rb');

        if ($handle === false) {
            throw new RuntimeException('The backup file could not be reopened for verification.');
        }

        $bytes = 0;

        try {
            while (! gzeof($handle)) {
                $buffer = gzread($handle, 1024 * 1024);

                if ($buffer === false) {
                    throw new RuntimeException('The backup file is corrupted and was discarded.');
                }

                $bytes += strlen($buffer);
            }
        } finally {
            gzclose($handle);
        }

        if ($bytes !== $expectedBytes) {
            throw new RuntimeException(
                "The backup file is incomplete and was discarded ({$bytes} of {$expectedBytes} bytes)."
            );
        }
    }
}
