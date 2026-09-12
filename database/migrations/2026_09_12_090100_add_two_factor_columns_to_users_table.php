<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional per-user TOTP 2FA (spec §32 — "recommended for Owner/Admin",
     * not mandatory). two_factor_secret/two_factor_recovery_codes are
     * deliberately NOT added to User's #[Fillable] list — they're only
     * ever written via a direct ->update()/->forceFill() call from
     * TwoFactorAuthService/TwoFactorSettingsController, never from a
     * mass-assigned request, so there's no way for a user to self-enable
     * 2FA (or plant a secret) by crafting an extra form field.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('two_factor_enabled')->default(false)->after('is_active');
            $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_enabled', 'two_factor_secret', 'two_factor_confirmed_at', 'two_factor_recovery_codes']);
        });
    }
};
