<?php

namespace Database\Factories;

use App\Models\MealTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MealTemplate>
 */
class MealTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'meal_time' => 'breakfast',
            'calories' => 400,
            'protein_g' => 20,
            'fat_g' => 15,
            'carbs_g' => 45,
            'name' => ['en' => $name, 'pl' => $name],
            'description' => ['en' => $this->faker->sentence(), 'pl' => $this->faker->sentence()],
        ];
    }
}
