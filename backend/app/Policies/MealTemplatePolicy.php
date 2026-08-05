<?php

namespace App\Policies;

use App\Models\MealTemplate;
use App\Models\User;

class MealTemplatePolicy
{
    /**
     * Whether the user may manage the meal template catalogue.
     *
     * A global catalogue, not a per-user resource — every ability reduces to
     * the same admin check, mirroring ExercisePolicy.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, MealTemplate $mealTemplate): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, MealTemplate $mealTemplate): bool
    {
        return $user->is_admin;
    }
}
