<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The actual per-purchase (or admin-granted) access record — what a
     * specific customer email actually has, as opposed to
     * `edition_entitlements` which just describes what an edition would
     * grant if bought.
     */
    public function up(): void
    {
        Schema::create('customer_entitlements', function (Blueprint $table) {
            $table->id();

            // Null order/order_item = a pure admin comp, not tied to a purchase.
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('app_id')->constrained()->cascadeOnDelete();
            $table->foreignId('app_edition_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
            $table->enum('access_type', ['download', 'web_access']);

            $table->string('customer_email')->index();

            $table->enum('status', ['active', 'revoked'])->default('active');
            $table->enum('source', ['purchase', 'admin_grant'])->default('purchase');

            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('revoked_reason')->nullable();
            $table->timestamp('revoked_at')->nullable();

            $table->timestamps();

            $table->index(['customer_email', 'app_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_entitlements');
    }
};
