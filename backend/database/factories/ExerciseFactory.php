<?php

namespace Database\Factories;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'type' => 'strength',
            'location' => 'gym',
            'difficulty' => 'beginner',
            'muscle_group' => 'chest',
            'sets' => 3,
            'reps' => 10,
            'duration_minutes' => null,
            'name' => ['en' => $name, 'pl' => $name],
            'instructions' => ['en' => $this->faker->sentence(), 'pl' => $this->faker->sentence()],
        ];
    }

    public function cardio(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'cardio',
            'muscle_group' => null,
            'sets' => null,
            'reps' => null,
            'duration_minutes' => 30,
        ]);
    }
}
