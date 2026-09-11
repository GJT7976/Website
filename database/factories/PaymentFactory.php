<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'provider' => 'stripe',
            'provider_payment_id' => 'pi_'.$this->faker->uuid(),
            'amount_cents' => 1000,
            'currency' => 'CAD',
            'status' => 'succeeded',
        ];
    }
}
