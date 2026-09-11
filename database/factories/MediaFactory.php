<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'disk' => 'public',
            'path' => 'media/uploads/'.$this->faker->uuid().'.png',
            'mime' => 'image/png',
            'size' => $this->faker->numberBetween(1000, 500000),
            'width' => 512,
            'height' => 512,
            'alt_text' => $this->faker->sentence(3),
            'title' => $this->faker->words(2, true),
        ];
    }
}
