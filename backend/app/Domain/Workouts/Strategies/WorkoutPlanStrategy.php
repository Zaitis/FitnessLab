<?php

namespace App\Domain\Workouts\Strategies;

use App\Domain\Workouts\Criteria\WorkoutPlanCriteria;
use App\Domain\Workouts\ValueObjects\GeneratedWorkoutPlan;

interface WorkoutPlanStrategy
{
    public function supports(WorkoutPlanCriteria $criteria): bool;

    public function generate(WorkoutPlanCriteria $criteria): GeneratedWorkoutPlan;
}
