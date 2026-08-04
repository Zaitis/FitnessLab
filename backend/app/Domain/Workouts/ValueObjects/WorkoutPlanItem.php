<?php

namespace App\Domain\Workouts\ValueObjects;

use App\Domain\Workouts\Enums\ExerciseType;

final readonly class WorkoutPlanItem
{
    /**
     * @param  array<string, string>  $name
     * @param  array<string, string>  $instructions
     */
    public function __construct(
        public int $day,
        public ExerciseType $type,
        public array $name,
        public array $instructions,
        public ?int $sets,
        public ?int $reps,
        public ?int $durationMinutes,
    ) {}

    public static function fromCatalogueExercise(int $day, CatalogueExercise $exercise): self
    {
        return new self(
            day: $day,
            type: $exercise->type,
            name: $exercise->name,
            instructions: $exercise->instructions,
            sets: $exercise->sets,
            reps: $exercise->reps,
            durationMinutes: $exercise->durationMinutes,
        );
    }
}
