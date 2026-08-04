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
 * A lighter version of the muscle-gain split — one strength exercise a day,
 * rotating groups — with cardio folded in on alternating days rather than
 * every day (fat loss) or never (muscle gain).
 */
final class MaintenanceWorkoutPlanStrategy implements WorkoutPlanStrategy
{
    public function __construct(private readonly ExerciseCatalogue $catalogue) {}

    public function supports(WorkoutPlanCriteria $criteria): bool
    {
        return $criteria->goal === Goal::Maintenance;
    }

    public function generate(WorkoutPlanCriteria $criteria): GeneratedWorkoutPlan
    {
        $groups = MuscleGroup::cases();

        $cardio = $this->catalogue->matching(new ExerciseFilter(
            type: ExerciseType::Cardio,
            maxDifficulty: $criteria->experienceLevel,
        ));

        $items = [];

        for ($day = 1; $day <= $criteria->daysPerWeek; $day++) {
            $group = $groups[($day - 1) % count($groups)];

            $strength = $this->catalogue->matching(new ExerciseFilter(
                type: ExerciseType::Strength,
                maxDifficulty: $criteria->experienceLevel,
                location: $criteria->location,
                muscleGroup: $group,
            ));

            if ($exercise = $strength->nth(0)) {
                $items[] = WorkoutPlanItem::fromCatalogueExercise($day, $exercise);
            }

            if ($day % 2 === 1 && $exercise = $cardio->nth(intdiv($day - 1, 2))) {
                $items[] = WorkoutPlanItem::fromCatalogueExercise($day, $exercise);
            }
        }

        return new GeneratedWorkoutPlan($items);
    }
}
