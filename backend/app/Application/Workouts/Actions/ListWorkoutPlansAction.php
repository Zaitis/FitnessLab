<?php

namespace App\Application\Workouts\Actions;

use App\Models\User;
use App\Models\WorkoutPlan;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListWorkoutPlansAction
{
    /**
     * @return LengthAwarePaginator<int, WorkoutPlan>
     */
    public function execute(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $user->workoutPlans()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
