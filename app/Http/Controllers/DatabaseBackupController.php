<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;

class DatabaseBackupController extends Controller
{
    private DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    private function statusFor(\Throwable $th): int
    {
        $code = (int) $th->getCode();

        return ($code >= 400 && $code <= 599) ? $code : 500;
    }

    public function index()
    {
        try {
            return response()->success([
                'backups' => $this->backupService->all(),
                'keep_limit' => $this->backupService->keepLimit(),
            ], 'Backups retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $this->statusFor($th));
        }
    }

    /** Run a new dump; the oldest archives beyond the limit are dropped. */
    public function store()
    {
        // If the browser gives up waiting, finish the request anyway so the
        // dump and the credentials file are still cleaned up.
        ignore_user_abort(true);

        try {
            $backup = $this->backupService->create();

            return response()->success($backup, 'Backup created successfully', 201);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $this->statusFor($th));
        }
    }

    public function download($name)
    {
        try {
            return response()->download($this->backupService->path($name), $name, [
                'Content-Type' => 'application/gzip',
            ]);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $this->statusFor($th));
        }
    }

    public function destroy($name)
    {
        try {
            $this->backupService->delete($name);

            return response()->success(null, 'Backup deleted successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $this->statusFor($th));
        }
    }
}
