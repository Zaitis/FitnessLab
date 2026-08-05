<?php

namespace App\Application\Nutrition\Actions;

use App\Models\MealTemplate;

final class UpdateMealTemplateAction
{
    /**
     * @param  array<string, mixed>  $data  validated attributes, keyed like MealTemplate::$fillable
     */
    public function execute(MealTemplate $mealTemplate, array $data): MealTemplate
    {
        $mealTemplate->update($data);

        return $mealTemplate;
    }
}
