<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Non-interactive counterpart to `make:admin`, for environments with no
 * interactive shell (e.g. a Hostinger Cron Job run once after initial
 * deploy, instead of SSH). Reads credentials from BOOTSTRAP_ADMIN_*
 * environment variables rather than prompting — never hardcode credentials
 * in this file. Idempotent: refuses to run if any owner account already
 * exists, so leaving this command in the codebase (or a stray cron entry)
 * after first use is safe — it cannot create a second owner or overwrite
 * one, and BOOTSTRAP_ADMIN_PASSWORD should be removed from .env after use
 * regardless.
 */
class BootstrapAdminCommand extends Command
{
    protected $signature = 'admin:bootstrap';

    protected $description = 'One-time, non-interactive owner-account creation from BOOTSTRAP_ADMIN_* env vars (for hosts with no SSH). Refuses to run if an owner already exists.';

    public function handle(): int
    {
        if (User::where('role', 'owner')->exists()) {
            $this->error('An owner account already exists — refusing to run. Use the admin panel to manage users from here.');

            return self::FAILURE;
        }

        $name = env('BOOTSTRAP_ADMIN_NAME');
        $email = env('BOOTSTRAP_ADMIN_EMAIL');
        $password = env('BOOTSTRAP_ADMIN_PASSWORD');

        if (! $name || ! $email || ! $password) {
            $this->error('Set BOOTSTRAP_ADMIN_NAME, BOOTSTRAP_ADMIN_EMAIL and BOOTSTRAP_ADMIN_PASSWORD in .env before running this.');

            return self::FAILURE;
        }

        if (strlen($password) < 12) {
            $this->error('BOOTSTRAP_ADMIN_PASSWORD must be at least 12 characters.');

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'owner',
            'is_active' => true,
            'two_factor_enabled' => false,
            'email_verified_at' => now(),
        ]);

        $this->info("Owner account created: {$user->email}.");
        $this->line('Remove BOOTSTRAP_ADMIN_* from .env now that this has run.');

        return self::SUCCESS;
    }
}
