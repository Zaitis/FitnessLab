<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;

class ExercisePolicy
{
    /**
     * Whether the user may manage the exercise catalogue.
     *
     * A global catalogue, not a per-user resource — every ability reduces to
     * the same admin check, unlike WorkoutPlan's per-owner authorization.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, Exercise $exercise): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, Exercise $exercise): bool
    {
        return $user->is_admin;
    }
}
