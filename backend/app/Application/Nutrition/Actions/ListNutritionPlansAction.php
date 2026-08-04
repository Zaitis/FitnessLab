<?php

namespace App\Application\Nutrition\Actions;

use App\Models\NutritionPlan;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListNutritionPlansAction
{
    /**
     * @return LengthAwarePaginator<int, NutritionPlan>
     */
    public function execute(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $user->nutritionPlans()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
