<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One-time codes for the self-service device-management flow: the
     * customer enters their email + license key, gets emailed a code (or
     * signed link) at customer_email, and exchanging a valid unexpired
     * code for a signed management URL happens in
     * App\Services\LicenseService — never trusting email/license-key input
     * alone to reveal or change device activations.
     */
    public function up(): void
    {
        Schema::create('license_verification_codes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('license_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();

            $table->timestamps();

            $table->index(['license_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_verification_codes');
    }
};
