<?php

namespace App\Http\Requests\Admin;

use App\Domain\Workouts\Enums\ExerciseLocation;
use App\Domain\Workouts\Enums\ExerciseType;
use App\Domain\Workouts\Enums\ExperienceLevel;
use App\Domain\Workouts\Enums\MuscleGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared by store and update — an admin edit form always submits the whole
 * record, so there is no partial-update variant to diverge from this one.
 */
class ExerciseRequest extends FormRequest
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
        $isStrength = fn () => $this->input('type') === ExerciseType::Strength->value;
        $isCardio = fn () => $this->input('type') === ExerciseType::Cardio->value;

        $rules = [
            'type' => ['required', Rule::enum(ExerciseType::class)],
            'location' => ['required', Rule::enum(ExerciseLocation::class)],
            'difficulty' => ['required', Rule::enum(ExperienceLevel::class)],
            // Strength exercises carry a muscle group and a sets/reps target;
            // cardio carries a duration instead — enforced both ways so a
            // cardio row can't accidentally keep a stale muscle_group, and
            // vice versa.
            'muscle_group' => [
                'nullable', Rule::enum(MuscleGroup::class),
                Rule::requiredIf($isStrength), Rule::prohibitedIf($isCardio),
            ],
            'sets' => [
                'nullable', 'integer', 'min:1', 'max:20',
                Rule::requiredIf($isStrength), Rule::prohibitedIf($isCardio),
            ],
            'reps' => [
                'nullable', 'integer', 'min:1', 'max:100',
                Rule::requiredIf($isStrength), Rule::prohibitedIf($isCardio),
            ],
            'duration_minutes' => [
                'nullable', 'integer', 'min:1', 'max:180',
                Rule::requiredIf($isCardio), Rule::prohibitedIf($isStrength),
            ],
            'name' => ['required', 'array'],
            'instructions' => ['required', 'array'],
        ];

        foreach (config('supported_locales.supported') as $locale) {
            $rules["name.{$locale}"] = ['required', 'string', 'max:255'];
            $rules["instructions.{$locale}"] = ['required', 'string', 'max:2000'];
        }

        return $rules;
    }
}
