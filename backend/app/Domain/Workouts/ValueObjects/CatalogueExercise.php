<?php

namespace App\Domain\Workouts\ValueObjects;

use App\Domain\Workouts\Enums\ExerciseLocation;
use App\Domain\Workouts\Enums\ExerciseType;
use App\Domain\Workouts\Enums\ExperienceLevel;
use App\Domain\Workouts\Enums\MuscleGroup;

/**
 * One catalogue row, translated into every supported locale up front —
 * this is what lets a generated plan snapshot embed all of them at
 * generation time (ADR-005), without the domain layer knowing anything
 * about spatie/laravel-translatable or Eloquent.
 */
final readonly class CatalogueExercise
{
    /**
     * @param  array<string, string>  $name  locale => name
     * @param  array<string, string>  $instructions  locale => instructions
     */
    public function __construct(
        public int $id,
        public ExerciseType $type,
        public ExerciseLocation $location,
        public ExperienceLevel $difficulty,
        public ?MuscleGroup $muscleGroup,
        public ?int $sets,
        public ?int $reps,
        public ?int $durationMinutes,
        public array $name,
        public array $instructions,
    ) {}
}
