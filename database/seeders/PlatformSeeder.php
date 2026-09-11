<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            ['code' => 'android', 'name' => 'Android'],
            ['code' => 'windows', 'name' => 'Windows'],
            ['code' => 'web', 'name' => 'Web'],
            ['code' => 'pwa', 'name' => 'PWA'],
            ['code' => 'ios', 'name' => 'iOS'],
            ['code' => 'macos', 'name' => 'macOS'],
        ];

        foreach ($platforms as $index => $platform) {
            Platform::updateOrCreate(
                ['code' => $platform['code']],
                ['name' => $platform['name'], 'sort_order' => $index]
            );
        }
    }
}
