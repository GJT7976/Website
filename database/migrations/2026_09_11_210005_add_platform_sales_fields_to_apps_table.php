<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apps', function (Blueprint $table) {
            $table->enum('android_delivery_mode', ['direct', 'play', 'both', 'none'])->default('none')->after('google_play_url');
            $table->enum('windows_delivery_mode', ['direct', 'store', 'both', 'none'])->default('none')->after('microsoft_store_url');

            $table->boolean('web_available')->default(false)->after('windows_delivery_mode');
            $table->string('web_app_url')->nullable()->after('web_available');
            $table->boolean('web_login_required')->default(false)->after('web_app_url');

            $table->enum('license_type', ['personal', 'single_business', 'other'])->default('personal')->after('web_login_required');
            $table->string('license_label')->nullable()->after('license_type');

            $table->enum('update_policy', ['updates_included', 'major_upgrades_separate'])->default('updates_included')->after('license_label');
        });
    }

    public function down(): void
    {
        Schema::table('apps', function (Blueprint $table) {
            $table->dropColumn([
                'android_delivery_mode', 'windows_delivery_mode',
                'web_available', 'web_app_url', 'web_login_required',
                'license_type', 'license_label', 'update_policy',
            ]);
        });
    }
};
