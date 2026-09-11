<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:admin';

    /**
     * The console command description.
     */
    protected $description = 'Interactively create an administrator account (owner or content editor) — no default password is ever used.';

    public function handle(): int
    {
        $this->info('Create a Niagara Inde Apps administrator account.');

        $name = $this->ask('Name');

        $email = $this->ask('Email address');
        $emailValidator = Validator::make(['email' => $email], ['email' => ['required', 'email', 'unique:users,email']]);
        if ($emailValidator->fails()) {
            $this->error($emailValidator->errors()->first('email'));

            return self::FAILURE;
        }

        $role = $this->choice('Role', ['owner', 'content_editor'], 0);

        $password = $this->secret('Password (min. 12 characters, hidden as you type)');
        $confirm = $this->secret('Confirm password');

        if ($password !== $confirm) {
            $this->error('Passwords did not match.');

            return self::FAILURE;
        }

        $passwordValidator = Validator::make(['password' => $password], ['password' => ['required', 'string', 'min:12']]);
        if ($passwordValidator->fails()) {
            $this->error($passwordValidator->errors()->first('password'));

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->newLine();
        $this->info("Administrator account created: {$user->email} ({$role}).");
        $this->line('Sign in at /admin/login.');

        return self::SUCCESS;
    }
}
