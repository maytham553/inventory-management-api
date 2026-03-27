<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create
                            {--name= : User name}
                            {--email= : User email}
                            {--password= : User password}
                            {--generate-password : Auto-generate secure password}
                            {--type= : User type (Admin, User, SuperAdmin)}
                            {--token : Generate a Sanctum token}
                            {--token-name=cli-token : Token name if --token is passed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user from command line';

    public function handle(): int
    {
        $generatedPassword = null;
        $data = [
            'name' => $this->option('name'),
            'email' => $this->option('email'),
            'password' => $this->option('password'),
            'type' => $this->option('type'),
        ];

        if (!$data['name']) {
            if (!$this->input->isInteractive()) {
                $this->error('Missing required option: --name');
                return self::FAILURE;
            }
            $data['name'] = $this->ask('Name');
        }

        if (!$data['email']) {
            if (!$this->input->isInteractive()) {
                $this->error('Missing required option: --email');
                return self::FAILURE;
            }
            $data['email'] = $this->ask('Email');
        }

        if (!$data['password']) {
            if ($this->option('generate-password')) {
                $generatedPassword = Str::random(16);
                $data['password'] = $generatedPassword;
            }
        }

        if (!$data['password']) {
            if (!$this->input->isInteractive()) {
                $this->error('Missing required option: --password');
                return self::FAILURE;
            }
            if ($this->confirm('Auto-generate password?', false)) {
                $generatedPassword = Str::random(16);
                $data['password'] = $generatedPassword;
            } else {
                $password = $this->secret('Password (min 8 chars)');
                $passwordConfirmation = $this->secret('Confirm password');

                if ($password !== $passwordConfirmation) {
                    $this->error('Password confirmation does not match.');
                    return self::FAILURE;
                }

                $data['password'] = $password;
            }
        }

        if (!$data['type']) {
            if (!$this->input->isInteractive()) {
                $this->error('Missing required option: --type');
                return self::FAILURE;
            }
            $data['type'] = $this->choice('User type', ['Admin', 'User', 'SuperAdmin'], 1);
        }

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'type' => ['required', Rule::in(['Admin', 'User', 'SuperAdmin'])],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'type' => $data['type'],
        ]);

        $this->info('User created successfully.');
        $this->table(
            ['ID', 'Name', 'Email', 'Type'],
            [[$user->id, $user->name, $user->email, $user->type]]
        );
        if ($generatedPassword !== null) {
            $this->line('Generated password: ' . $generatedPassword);
        }

        if ($this->option('token')) {
            $tokenName = (string) $this->option('token-name');
            $token = $user->createToken($tokenName)->plainTextToken;
            $this->line('API token: ' . $token);
        }

        return self::SUCCESS;
    }
}
