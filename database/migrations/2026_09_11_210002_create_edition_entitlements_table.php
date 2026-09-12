<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A *template* of what an edition grants — e.g. the "Android + Windows
     * Bundle" edition has two rows here (android/download,
     * windows/download). Distinct from `customer_entitlements`, which is
     * the actual per-purchase grant created from these rows.
     */
    public function up(): void
    {
        Schema::create('edition_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
            $table->enum('access_type', ['download', 'web_access']);
            $table->timestamps();

            $table->unique(['app_edition_id', 'platform_id', 'access_type'], 'edition_entitlements_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edition_entitlements');
    }
};
