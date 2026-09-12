<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active', 'two_factor_enabled'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            // Laravel's built-in reversible cast (AES-256 via APP_KEY) — a
            // TOTP secret must be readable back in full to verify a code,
            // unlike a password.
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            // Recovery codes are individually bcrypt-hashed *and* the whole
            // column is encrypted — belt and suspenders (see
            // TwoFactorAuthService::generateRecoveryCodes()).
            'two_factor_recovery_codes' => 'encrypted:array',
        ];
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isContentEditor(): bool
    {
        return $this->role === 'content_editor';
    }

    /**
     * True only once enrollment has been confirmed with a valid code —
     * two_factor_confirmed_at stays null for an abandoned-mid-setup
     * secret, so a half-finished enrollment never silently locks a login.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && $this->two_factor_confirmed_at !== null;
    }
}
