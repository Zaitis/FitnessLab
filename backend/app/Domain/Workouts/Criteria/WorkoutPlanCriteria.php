<?php

namespace App\Domain\Workouts\Criteria;

use App\Domain\Workouts\Enums\ExerciseLocation;
use App\Domain\Workouts\Enums\ExperienceLevel;
use App\Domain\Workouts\Enums\Goal;
use InvalidArgumentException;

final readonly class WorkoutPlanCriteria
{
    public function __construct(
        public Goal $goal,
        public ExperienceLevel $experienceLevel,
        public int $daysPerWeek,
        public ExerciseLocation $location,
    ) {
        if ($daysPerWeek < 1 || $daysPerWeek > 6) {
            throw new InvalidArgumentException('daysPerWeek must be between 1 and 6.');
        }
    }
}
