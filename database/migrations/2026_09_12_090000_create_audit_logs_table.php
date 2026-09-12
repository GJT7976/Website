<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A general-purpose "who did what" trail (spec §33), deliberately
     * separate from the per-row granted_by/revoked_by stamping on
     * CustomerEntitlement (see EntitlementService) — that stamping stays
     * as-is; this table is for everything else. Rows are immutable: no
     * code anywhere updates or deletes them (see AuditLogController,
     * which exposes only index()), so updated_at is never written.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Null = a CLI/system action (e.g. a scheduled backup), not a
            // missing record — never assume a null actor means unlogged.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('action'); // dot-notation, e.g. "app.updated"

            // Polymorphic reference to the affected row, plus a human
            // snapshot that survives the row itself being later deleted.
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('resource_label')->nullable();

            $table->json('before')->nullable();
            $table->json('after')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
