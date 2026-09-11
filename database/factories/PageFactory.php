<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->unique()->words(2, true);

        return [
            'slug' => Str::slug($title),
            'title' => ucfirst($title),
            'published' => true,
        ];
    }
}
