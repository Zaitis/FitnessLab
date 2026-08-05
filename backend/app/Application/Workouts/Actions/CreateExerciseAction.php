<?php

namespace App\Application\Workouts\Actions;

use App\Models\Exercise;

final class CreateExerciseAction
{
    /**
     * @param  array<string, mixed>  $data  validated attributes, keyed like Exercise::$fillable
     */
    public function execute(array $data): Exercise
    {
        return Exercise::create($data);
    }
}
