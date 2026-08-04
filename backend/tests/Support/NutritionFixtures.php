<?php

namespace Tests\Support;

use App\Domain\Nutrition\Enums\MealTime;
use App\Domain\Nutrition\ValueObjects\CatalogueMeal;

/**
 * A small, fixed meal set for unit-testing the nutrition plan strategies
 * with no database: four meals per meal time, at varying calorie levels,
 * so sortedByProximityTo() has real variety to choose from.
 */
final class NutritionFixtures
{
    public static function catalogue(): InMemoryMealTemplateCatalogue
    {
        return new InMemoryMealTemplateCatalogue(self::meals());
    }

    /**
     * @return list<CatalogueMeal>
     */
    private static function meals(): array
    {
        $id = 1;
        $meals = [];

        foreach (MealTime::cases() as $mealTime) {
            foreach ([200, 350, 500, 650] as $calories) {
                $meals[] = self::meal($id++, $mealTime, $calories);
            }
        }

        return $meals;
    }

    private static function meal(int $id, MealTime $mealTime, int $calories): CatalogueMeal
    {
        $label = "{$mealTime->value} {$calories}kcal";

        return new CatalogueMeal(
            id: $id,
            mealTime: $mealTime,
            calories: $calories,
            proteinG: (int) round($calories * 0.3 / 4),
            fatG: (int) round($calories * 0.3 / 9),
            carbsG: (int) round($calories * 0.4 / 4),
            name: ['en' => "Meal ({$label})", 'pl' => "Posiłek ({$label})"],
            description: ['en' => "Description for {$label}.", 'pl' => "Opis dla {$label}."],
        );
    }
}
