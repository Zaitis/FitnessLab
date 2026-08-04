<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Nutrition\Contracts\MealTemplateCatalogue;
use App\Domain\Nutrition\Enums\MealTime;
use App\Domain\Nutrition\ValueObjects\CatalogueMeal;
use App\Domain\Nutrition\ValueObjects\MealCollection;
use App\Domain\Nutrition\ValueObjects\MealFilter;
use App\Models\MealTemplate;

final class EloquentMealTemplateCatalogue implements MealTemplateCatalogue
{
    public function matching(MealFilter $filter): MealCollection
    {
        $meals = MealTemplate::query()
            ->where('meal_time', $filter->mealTime->value)
            ->orderBy('id')
            ->get()
            ->map($this->toDomain(...))
            ->all();

        return new MealCollection($meals);
    }

    private function toDomain(MealTemplate $meal): CatalogueMeal
    {
        return new CatalogueMeal(
            id: $meal->id,
            mealTime: MealTime::from($meal->meal_time),
            calories: $meal->calories,
            proteinG: $meal->protein_g,
            fatG: $meal->fat_g,
            carbsG: $meal->carbs_g,
            name: $meal->getTranslations('name'),
            description: $meal->getTranslations('description'),
        );
    }
}
