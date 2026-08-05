<?php

namespace App\Application\Workouts\Actions;

use App\Models\Exercise;

final class DeleteExerciseAction
{
    /**
     * Generated plans store a snapshot of every item at generation time
     * (ADR-003), not a live reference to the catalogue row — deleting an
     * exercise here only removes it from future generation, it never
     * corrupts a plan that already exists.
     */
    public function execute(Exercise $exercise): void
    {
        $exercise->delete();
    }
}
