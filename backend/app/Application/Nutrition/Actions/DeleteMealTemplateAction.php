<?php

namespace App\Application\Nutrition\Actions;

use App\Models\MealTemplate;

final class DeleteMealTemplateAction
{
    /**
     * Generated plans store a snapshot of every item at generation time
     * (ADR-003), not a live reference to the catalogue row — deleting a
     * meal template here only removes it from future generation, it never
     * corrupts a plan that already exists.
     */
    public function execute(MealTemplate $mealTemplate): void
    {
        $mealTemplate->delete();
    }
}
