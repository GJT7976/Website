<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per device activated against a license (max `maximum_devices`
     * active rows per license, enforced in App\Services\LicenseService, not
     * here). `device_identifier_hash` is sha256 of the opaque installation
     * ID the app itself generates and stores — never a MAC address, IMEI,
     * Android serial, or Windows product key.
     */
    public function up(): void
    {
        Schema::create('license_devices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('license_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['android', 'windows']);
            $table->string('device_identifier_hash');
            $table->string('label')->nullable();
            $table->string('app_version')->nullable();

            $table->enum('status', ['active', 'deactivated'])->default('active');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_validated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->string('deactivated_reason')->nullable();

            $table->timestamps();

            $table->unique(['license_id', 'device_identifier_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_devices');
    }
};
