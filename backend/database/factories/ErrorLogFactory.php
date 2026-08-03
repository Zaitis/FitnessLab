<?php

namespace Database\Factories;

use App\Models\ErrorLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ErrorLog>
 */
class ErrorLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'level' => 'error',
            'message' => $this->faker->sentence(),
            'context' => [],
            'created_at' => now(),
        ];
    }
}
