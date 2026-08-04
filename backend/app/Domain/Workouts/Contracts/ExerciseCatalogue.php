<?php

namespace App\Domain\Workouts\Contracts;

use App\Domain\Workouts\ValueObjects\ExerciseCollection;
use App\Domain\Workouts\ValueObjects\ExerciseFilter;

interface ExerciseCatalogue
{
    public function matching(ExerciseFilter $filter): ExerciseCollection;
}
