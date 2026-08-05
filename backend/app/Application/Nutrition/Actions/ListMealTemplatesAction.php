<?php

namespace App\Application\Nutrition\Actions;

use App\Models\MealTemplate;
use Illuminate\Database\Eloquent\Collection;

final class ListMealTemplatesAction
{
    /**
     * Unpaginated, mirroring ListExercisesAction — the catalogue is a few
     * dozen rows, not a few thousand.
     *
     * @return Collection<int, MealTemplate>
     */
    public function execute(): Collection
    {
        return MealTemplate::query()
            ->orderBy('meal_time')
            ->orderBy('id')
            ->get();
    }
}
