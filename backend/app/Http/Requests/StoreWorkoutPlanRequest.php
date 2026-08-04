<?php

namespace App\Http\Requests;

use App\Domain\Workouts\Enums\ExerciseLocation;
use App\Domain\Workouts\Enums\ExperienceLevel;
use App\Domain\Workouts\Enums\Goal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkoutPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'goal' => ['required', Rule::enum(Goal::class)],
            'experience_level' => ['required', Rule::enum(ExperienceLevel::class)],
            'days_per_week' => ['required', 'integer', 'min:1', 'max:6'],
            'location' => ['required', Rule::enum(ExerciseLocation::class)],
        ];
    }
}
