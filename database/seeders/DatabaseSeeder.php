<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * No admin user is created here — the first administrator is created
     * securely via `php artisan make:admin` (see LOCAL_SETUP.md), so no
     * default/placeholder password ever exists (spec §31).
     */
    public function run(): void
    {
        $this->call([
            AppCategorySeeder::class,
            PlatformSeeder::class,
            AppSeeder::class,
            PageSeeder::class,
            SettingSeeder::class,
            FaqSeeder::class,
        ]);
    }
}
