<?php

namespace Tests\Support;

use App\Domain\Workouts\Contracts\ExerciseCatalogue;
use App\Domain\Workouts\ValueObjects\CatalogueExercise;
use App\Domain\Workouts\ValueObjects\ExerciseCollection;
use App\Domain\Workouts\ValueObjects\ExerciseFilter;

final class InMemoryExerciseCatalogue implements ExerciseCatalogue
{
    /**
     * @param  list<CatalogueExercise>  $exercises
     */
    public function __construct(private readonly array $exercises) {}

    public function matching(ExerciseFilter $filter): ExerciseCollection
    {
        return new ExerciseCollection(array_values(array_filter(
            $this->exercises,
            fn (CatalogueExercise $exercise) => $exercise->type === $filter->type
                && $exercise->difficulty->atMost($filter->maxDifficulty)
                && ($filter->location === null || $exercise->location === $filter->location)
                && ($filter->muscleGroup === null || $exercise->muscleGroup === $filter->muscleGroup),
        )));
    }
}
