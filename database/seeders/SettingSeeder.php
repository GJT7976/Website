<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site' => [
                'hero_title' => 'Niagara Inde Apps',
                'hero_subtitle' => 'Smart Tools for Real People',
                'hero_description' => 'We build practical, easy-to-use applications for small businesses, independent creators, and everyday users across Canada and beyond.',
                'footer_tagline' => 'Ideas. Innovation. Independent.',
                'seo_default_title' => 'Niagara Inde Apps — Smart Tools for Real People',
                'seo_default_description' => 'Independent Canadian app development based in Niagara, Ontario. Practical, offline-capable software for small businesses and everyday users.',
            ],
            'business' => [
                'business_name' => 'Niagara Inde Apps',
                'legal_name' => '', // owner action: fill in if operating under a registered legal name
                'address' => '',
                'city' => 'Niagara Region',
                'province' => 'ON',
                'postal_code' => '',
                'country' => 'CA',
                'email' => '', // owner action: business contact email
                'telephone' => '',
            ],
            'store' => [
                'default_currency' => 'CAD',
                'order_prefix' => 'NIA-',
            ],
        ];

        foreach ($settings as $group => $groupSettings) {
            foreach ($groupSettings as $key => $value) {
                Setting::set($key, $value, $group);
            }
        }
    }
}
