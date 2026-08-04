<?php

namespace App\Domain\Workouts\Strategies;

use App\Domain\Workouts\Contracts\ExerciseCatalogue;
use App\Domain\Workouts\Criteria\WorkoutPlanCriteria;
use App\Domain\Workouts\Enums\ExerciseType;
use App\Domain\Workouts\Enums\Goal;
use App\Domain\Workouts\ValueObjects\ExerciseFilter;
use App\Domain\Workouts\ValueObjects\GeneratedWorkoutPlan;
use App\Domain\Workouts\ValueObjects\WorkoutPlanItem;

/**
 * Every day mixes cardio with full-body strength work — burning calories is
 * the point, so cardio is never optional the way it is for the other goals.
 */
final class FatLossWorkoutPlanStrategy implements WorkoutPlanStrategy
{
    private const int STRENGTH_EXERCISES_PER_DAY = 2;

    public function __construct(private readonly ExerciseCatalogue $catalogue) {}

    public function supports(WorkoutPlanCriteria $criteria): bool
    {
        return $criteria->goal === Goal::FatLoss;
    }

    public function generate(WorkoutPlanCriteria $criteria): GeneratedWorkoutPlan
    {
        $cardio = $this->catalogue->matching(new ExerciseFilter(
            type: ExerciseType::Cardio,
            maxDifficulty: $criteria->experienceLevel,
        ));

        $strength = $this->catalogue->matching(new ExerciseFilter(
            type: ExerciseType::Strength,
            maxDifficulty: $criteria->experienceLevel,
            location: $criteria->location,
        ));

        $items = [];

        for ($day = 1; $day <= $criteria->daysPerWeek; $day++) {
            if ($exercise = $cardio->nth($day - 1)) {
                $items[] = WorkoutPlanItem::fromCatalogueExercise($day, $exercise);
            }

            for ($slot = 0; $slot < self::STRENGTH_EXERCISES_PER_DAY; $slot++) {
                $index = ($day - 1) * self::STRENGTH_EXERCISES_PER_DAY + $slot;

                if ($exercise = $strength->nth($index)) {
                    $items[] = WorkoutPlanItem::fromCatalogueExercise($day, $exercise);
                }
            }
        }

        return new GeneratedWorkoutPlan($items);
    }
}
