<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('app_edition_id')->nullable()->after('app_id')->constrained()->nullOnDelete();

            // Snapshotted at purchase time, like app_name_snapshot — never
            // re-derived from the live edition/app record for a historical
            // order. Null for legacy pre-edition orders.
            $table->string('edition_name_snapshot')->nullable()->after('app_name_snapshot');
            $table->json('included_platforms_snapshot')->nullable()->after('edition_name_snapshot');
            $table->string('license_label_snapshot')->nullable()->after('included_platforms_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('app_edition_id');
            $table->dropColumn(['edition_name_snapshot', 'included_platforms_snapshot', 'license_label_snapshot']);
        });
    }
};
