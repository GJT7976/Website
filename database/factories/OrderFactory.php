<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => 'NIA-'.$this->faker->unique()->numerify('########'),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
            'billing_address' => $this->faker->streetAddress(),
            'billing_city' => $this->faker->city(),
            'billing_province' => 'ON',
            'billing_postal_code' => 'A1A 1A1',
            'billing_country' => 'CA',
            'subtotal_cents' => 499,
            'discount_cents' => 0,
            'tax_cents' => 65,
            'total_cents' => 564,
            'currency' => 'CAD',
            'payment_provider' => 'stripe',
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ];
    }
}
