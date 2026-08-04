<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Workouts\Contracts\ExerciseCatalogue;
use App\Domain\Workouts\Enums\ExerciseLocation;
use App\Domain\Workouts\Enums\ExerciseType;
use App\Domain\Workouts\Enums\ExperienceLevel;
use App\Domain\Workouts\Enums\MuscleGroup;
use App\Domain\Workouts\ValueObjects\CatalogueExercise;
use App\Domain\Workouts\ValueObjects\ExerciseCollection;
use App\Domain\Workouts\ValueObjects\ExerciseFilter;
use App\Models\Exercise;

final class EloquentExerciseCatalogue implements ExerciseCatalogue
{
    public function matching(ExerciseFilter $filter): ExerciseCollection
    {
        $allowedDifficulties = array_values(array_map(
            fn (ExperienceLevel $level) => $level->value,
            array_filter(
                ExperienceLevel::cases(),
                fn (ExperienceLevel $level) => $level->atMost($filter->maxDifficulty),
            ),
        ));

        $query = Exercise::query()
            ->where('type', $filter->type->value)
            ->whereIn('difficulty', $allowedDifficulties)
            ->orderBy('id');

        if ($filter->location !== null) {
            $query->where('location', $filter->location->value);
        }

        if ($filter->muscleGroup !== null) {
            $query->where('muscle_group', $filter->muscleGroup->value);
        }

        return new ExerciseCollection(
            $query->get()->map($this->toDomain(...))->all()
        );
    }

    private function toDomain(Exercise $exercise): CatalogueExercise
    {
        return new CatalogueExercise(
            id: $exercise->id,
            type: ExerciseType::from($exercise->type),
            location: ExerciseLocation::from($exercise->location),
            difficulty: ExperienceLevel::from($exercise->difficulty),
            muscleGroup: $exercise->muscle_group !== null ? MuscleGroup::from($exercise->muscle_group) : null,
            sets: $exercise->sets,
            reps: $exercise->reps,
            durationMinutes: $exercise->duration_minutes,
            name: $exercise->getTranslations('name'),
            instructions: $exercise->getTranslations('instructions'),
        );
    }
}
