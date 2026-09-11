<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('app_id')->nullable()->constrained()->nullOnDelete();

            // Snapshotted at time of purchase — never re-read the live
            // app record for a historical order (price/name may change).
            $table->string('app_name_snapshot');
            $table->unsignedInteger('unit_price_cents');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('line_subtotal_cents');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
