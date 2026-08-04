<?php

namespace App\Domain\Workouts\ValueObjects;

use App\Domain\Workouts\Enums\ExerciseLocation;
use App\Domain\Workouts\Enums\ExerciseType;
use App\Domain\Workouts\Enums\ExperienceLevel;
use App\Domain\Workouts\Enums\MuscleGroup;

/**
 * A location of null means "don't filter by location" — used for cardio,
 * which isn't meaningfully gym-or-home the way strength training is.
 */
final readonly class ExerciseFilter
{
    public function __construct(
        public ExerciseType $type,
        public ExperienceLevel $maxDifficulty,
        public ?ExerciseLocation $location = null,
        public ?MuscleGroup $muscleGroup = null,
    ) {}
}
