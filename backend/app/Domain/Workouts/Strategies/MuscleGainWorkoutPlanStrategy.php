<?php

namespace App\Domain\Workouts\Strategies;

use App\Domain\Workouts\Contracts\ExerciseCatalogue;
use App\Domain\Workouts\Criteria\WorkoutPlanCriteria;
use App\Domain\Workouts\Enums\ExerciseType;
use App\Domain\Workouts\Enums\Goal;
use App\Domain\Workouts\Enums\MuscleGroup;
use App\Domain\Workouts\ValueObjects\ExerciseFilter;
use App\Domain\Workouts\ValueObjects\GeneratedWorkoutPlan;
use App\Domain\Workouts\ValueObjects\WorkoutPlanItem;

/**
 * A muscle-group split rotating one group per day — no cardio, since the
 * goal is added muscle, not calorie burn.
 */
final class MuscleGainWorkoutPlanStrategy implements WorkoutPlanStrategy
{
    private const int STRENGTH_EXERCISES_PER_DAY = 2;

    public function __construct(private readonly ExerciseCatalogue $catalogue) {}

    public function supports(WorkoutPlanCriteria $criteria): bool
    {
        return $criteria->goal === Goal::MuscleGain;
    }

    public function generate(WorkoutPlanCriteria $criteria): GeneratedWorkoutPlan
    {
        $groups = MuscleGroup::cases();
        $items = [];

        for ($day = 1; $day <= $criteria->daysPerWeek; $day++) {
            $group = $groups[($day - 1) % count($groups)];

            $exercises = $this->catalogue->matching(new ExerciseFilter(
                type: ExerciseType::Strength,
                maxDifficulty: $criteria->experienceLevel,
                location: $criteria->location,
                muscleGroup: $group,
            ));

            for ($slot = 0; $slot < self::STRENGTH_EXERCISES_PER_DAY; $slot++) {
                if ($exercise = $exercises->nth($slot)) {
                    $items[] = WorkoutPlanItem::fromCatalogueExercise($day, $exercise);
                }
            }
        }

        return new GeneratedWorkoutPlan($items);
    }
}
