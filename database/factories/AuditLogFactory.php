<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => 'app.updated',
            'auditable_type' => null,
            'auditable_id' => null,
            'resource_label' => fake()->words(2, true),
            'before' => null,
            'after' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ];
    }
}
