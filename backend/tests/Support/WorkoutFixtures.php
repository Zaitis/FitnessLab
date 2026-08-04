<?php

namespace Tests\Support;

use App\Domain\Workouts\Enums\ExerciseLocation;
use App\Domain\Workouts\Enums\ExerciseType;
use App\Domain\Workouts\Enums\ExperienceLevel;
use App\Domain\Workouts\Enums\MuscleGroup;
use App\Domain\Workouts\ValueObjects\CatalogueExercise;

/**
 * A small, fixed exercise set for unit-testing the workout plan strategies
 * with no database: two beginner strength exercises per muscle group (the
 * minimum a strategy ever needs to avoid picking the same exercise twice in
 * one day), plus one bonus higher-difficulty exercise and one cardio
 * exercise per difficulty tier to exercise the maxDifficulty filter.
 */
final class WorkoutFixtures
{
    public static function catalogue(): InMemoryExerciseCatalogue
    {
        return new InMemoryExerciseCatalogue(self::exercises());
    }

    /**
     * @return list<CatalogueExercise>
     */
    private static function exercises(): array
    {
        $id = 1;
        $exercises = [];

        foreach (MuscleGroup::cases() as $group) {
            foreach ([1, 2] as $variant) {
                $exercises[] = self::strength($id++, $group, ExperienceLevel::Beginner, "beginner {$group->value} {$variant}");
            }
        }

        $exercises[] = self::strength($id++, MuscleGroup::Legs, ExperienceLevel::Advanced, 'advanced legs bonus');

        foreach (ExperienceLevel::cases() as $level) {
            $exercises[] = self::cardio($id++, $level, "{$level->value} cardio");
        }

        return $exercises;
    }

    private static function strength(int $id, MuscleGroup $group, ExperienceLevel $difficulty, string $label): CatalogueExercise
    {
        return new CatalogueExercise(
            id: $id,
            type: ExerciseType::Strength,
            location: ExerciseLocation::Gym,
            difficulty: $difficulty,
            muscleGroup: $group,
            sets: 3,
            reps: 10,
            durationMinutes: null,
            name: ['en' => "Strength ({$label})", 'pl' => "Siłowe ({$label})"],
            instructions: ['en' => "Instructions for {$label}.", 'pl' => "Instrukcje dla {$label}."],
        );
    }

    private static function cardio(int $id, ExperienceLevel $difficulty, string $label): CatalogueExercise
    {
        return new CatalogueExercise(
            id: $id,
            type: ExerciseType::Cardio,
            location: ExerciseLocation::Outdoor,
            difficulty: $difficulty,
            muscleGroup: null,
            sets: null,
            reps: null,
            durationMinutes: 30,
            name: ['en' => "Cardio ({$label})", 'pl' => "Cardio ({$label})"],
            instructions: ['en' => "Instructions for {$label}.", 'pl' => "Instrukcje dla {$label}."],
        );
    }
}
