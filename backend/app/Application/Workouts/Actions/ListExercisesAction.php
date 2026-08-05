<?php

namespace App\Application\Workouts\Actions;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Collection;

final class ListExercisesAction
{
    /**
     * Unpaginated: the catalogue is a few dozen rows, not a few thousand —
     * an admin table listing all of them at once needs no pagination.
     *
     * @return Collection<int, Exercise>
     */
    public function execute(): Collection
    {
        return Exercise::query()
            ->orderBy('type')
            ->orderBy('muscle_group')
            ->orderBy('id')
            ->get();
    }
}
