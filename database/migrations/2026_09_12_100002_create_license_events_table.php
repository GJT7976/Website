<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only activation/reset history for a license — backs the
     * self-service reset rate limiting in App\Services\LicenseService and
     * the admin "view license history" requirement. Deliberately separate
     * from the general audit_logs table, which stays scoped to admin
     * actions (see ARCHITECTURE.md) — these rows are customer/API-driven.
     */
    public function up(): void
    {
        Schema::create('license_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('license_id')->constrained()->cascadeOnDelete();
            $table->foreignId('license_device_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', [
                'activated', 'validated', 'deactivated',
                'rejected_max_devices', 'rejected_platform', 'rejected_status', 'rejected_product',
                'reset_rate_limited',
            ]);
            $table->enum('platform', ['android', 'windows'])->nullable();
            $table->string('ip_address')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index(['license_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_events');
    }
};
