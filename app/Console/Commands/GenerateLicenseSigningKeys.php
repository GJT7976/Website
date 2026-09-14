<?php

namespace App\Console\Commands;

use App\Services\LicenseTokenSigner;
use Illuminate\Console\Command;

/**
 * One-time setup: generates the Ed25519 keypair used to sign license
 * entitlement tokens (§14). Deliberately does NOT write to .env
 * automatically — printing the values and letting the operator paste them
 * in avoids ever silently overwriting a production key.
 */
class GenerateLicenseSigningKeys extends Command
{
    protected $signature = 'license:keys:generate';

    protected $description = 'Generate a new Ed25519 keypair for signing offline license entitlement tokens';

    public function handle(): int
    {
        if (config('licensing.signing_private_key')) {
            if (! $this->confirm('LICENSE_SIGNING_PRIVATE_KEY is already set. Generating a new keypair will invalidate every entitlement token already cached by installed apps until they next go online. Continue?', false)) {
                return self::FAILURE;
            }
        }

        $keys = LicenseTokenSigner::generateKeypair();

        $this->newLine();
        $this->line('Add these to this app\'s <fg=yellow>.env</> (never commit them, never ship the private key to a client):');
        $this->newLine();
        $this->line("LICENSE_SIGNING_PRIVATE_KEY={$keys['secret']}");
        $this->line("LICENSE_SIGNING_PUBLIC_KEY={$keys['public']}");
        $this->newLine();
        $this->line('The <fg=yellow>public</> key above is safe to embed in each Flutter app to verify entitlement tokens offline — see LICENSE_SYSTEM.md.');

        return self::SUCCESS;
    }
}
