<?php

namespace Database\Seeders;

use App\Models\AppCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AppCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Business',
            'Food & Recipes',
            'Productivity',
            'Utilities',
            'Education',
            'Lifestyle',
            'Other',
        ];

        foreach ($categories as $index => $name) {
            AppCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $index]
            );
        }
    }
}
