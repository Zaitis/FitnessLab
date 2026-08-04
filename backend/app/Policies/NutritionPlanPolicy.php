<?php

namespace App\Policies;

use App\Models\NutritionPlan;
use App\Models\User;

class NutritionPlanPolicy
{
    public function view(User $user, NutritionPlan $nutritionPlan): bool
    {
        return $nutritionPlan->user_id === $user->id;
    }
}
