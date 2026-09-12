<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_editions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->constrained()->cascadeOnDelete();

            $table->string('name'); // e.g. "Android", "Android + Windows Bundle"
            $table->string('slug'); // e.g. android, windows, android-windows, web, complete
            $table->text('description')->nullable();

            // Money in integer cents, never floats.
            $table->unsignedInteger('price_cents');
            $table->char('currency', 3)->default('CAD');

            $table->boolean('active')->default(true);
            $table->boolean('featured')->default(false); // "BEST VALUE" badge
            $table->unsignedInteger('sort_order')->default(0);

            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();

            $table->timestamps();

            $table->unique(['app_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_editions');
    }
};
