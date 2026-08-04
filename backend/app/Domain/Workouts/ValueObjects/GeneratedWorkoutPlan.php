<?php

namespace App\Domain\Workouts\ValueObjects;

final readonly class GeneratedWorkoutPlan
{
    /**
     * @param  list<WorkoutPlanItem>  $items
     */
    public function __construct(public array $items) {}
}
