<?php

namespace Database\Factories;

use App\Models\BmiMeasurement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BmiMeasurement>
 */
class BmiMeasurementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $weightKg = $this->faker->randomFloat(1, 45, 120);
        $heightCm = $this->faker->randomFloat(1, 150, 200);
        $bmiValue = round($weightKg / (($heightCm / 100) ** 2), 1);

        return [
            'user_id' => User::factory(),
            'weight_kg' => $weightKg,
            'height_cm' => $heightCm,
            'bmi_value' => $bmiValue,
            'category' => 'normal',
            'measured_on' => $this->faker->date(),
        ];
    }
}
