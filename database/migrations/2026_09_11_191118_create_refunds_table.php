<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stripe_refund_id')->nullable()->index();
            $table->unsignedInteger('amount_cents');
            $table->string('reason')->nullable();
            // Null when the refund originated in the Stripe dashboard
            // rather than being initiated from this admin.
            $table->foreignId('administrator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'succeeded', 'failed'])->default('succeeded');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
