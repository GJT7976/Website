<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // Explicit rather than relying on the DB column default: Laravel's
            // attribute-based #[Fillable(...)] inserts NULL for any fillable
            // column omitted from create()'s attributes, which bypasses the
            // migration's ->default(true) for is_active (and ->default(false)
            // for two_factor_enabled).
            'role' => 'content_editor',
            'is_active' => true,
            'two_factor_enabled' => false,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'owner']);
    }

    public function contentEditor(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'content_editor']);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_enabled' => true,
            'two_factor_secret' => (new Google2FA)->generateSecretKey(),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
