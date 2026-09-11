<?php

namespace Database\Factories;

use App\Models\App;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<App>
 */
class AppFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true).' App';

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1000, 999999),
            'tagline' => $this->faker->sentence(4),
            'short_description' => $this->faker->sentence(12),
            'long_description' => $this->faker->paragraphs(2, true),
            'version' => '1.0',
            'is_free' => true,
            'currency' => 'CAD',
            'status' => 'draft',
            'is_featured' => false,
            'demo_enabled' => false,
        ];
    }
}
