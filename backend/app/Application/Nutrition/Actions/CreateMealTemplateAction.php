<?php

namespace App\Application\Nutrition\Actions;

use App\Models\MealTemplate;

final class CreateMealTemplateAction
{
    /**
     * @param  array<string, mixed>  $data  validated attributes, keyed like MealTemplate::$fillable
     */
    public function execute(array $data): MealTemplate
    {
        return MealTemplate::create($data);
    }
}
