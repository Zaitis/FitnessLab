<?php

namespace App\Application\Workouts\Actions;

use App\Models\Exercise;

final class UpdateExerciseAction
{
    /**
     * @param  array<string, mixed>  $data  validated attributes, keyed like Exercise::$fillable
     */
    public function execute(Exercise $exercise, array $data): Exercise
    {
        $exercise->update($data);

        return $exercise;
    }
}
