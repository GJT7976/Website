<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One permanent license per purchased order item whose edition grants
     * Android and/or Windows *download* access — a Web/PWA-only edition
     * entitlement never gets a license. `order_item_id` is unique so
     * App\Services\LicenseService::createFromOrder() can firstOrCreate()
     * on it and stay replay-safe against a re-delivered Stripe webhook.
     */
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('app_id')->constrained()->cascadeOnDelete();
            $table->foreignId('app_edition_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->unique()->constrained()->nullOnDelete();

            // sha256($rawKey) — the same lookup-by-hash pattern Laravel
            // Sanctum uses for API tokens. This, not the encrypted column
            // below, is what activation/validation look the key up by.
            $table->string('license_key_hash')->unique();

            // AES-256 (Laravel's `encrypted` cast, via APP_KEY) — NOT
            // plain text, but reversible, unlike the hash above. Exists
            // only so an admin can use "Resend license email" (§23)
            // without asking the customer to buy again; the public
            // activate/validate API only ever compares against the hash
            // and never reads this column.
            $table->text('license_key_encrypted');

            $table->string('customer_name')->nullable();
            $table->string('customer_email')->index();

            $table->enum('platform_entitlement', ['android_only', 'windows_only', 'android_windows_bundle']);

            $table->unsignedInteger('price_paid_cents');
            $table->char('currency', 3);
            $table->string('transaction_id')->nullable();
            $table->string('payment_provider')->default('stripe');
            $table->timestamp('purchase_date')->nullable();

            $table->unsignedTinyInteger('maximum_devices')->default(2);

            $table->enum('status', ['active', 'revoked', 'refunded', 'chargeback', 'disabled'])->default('active');

            $table->timestamps();

            $table->index(['customer_email', 'app_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
