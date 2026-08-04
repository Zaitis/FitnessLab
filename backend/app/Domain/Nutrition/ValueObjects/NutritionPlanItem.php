<?php

namespace App\Domain\Nutrition\ValueObjects;

use App\Domain\Nutrition\Enums\MealTime;

final readonly class NutritionPlanItem
{
    /**
     * @param  array<string, string>  $name
     * @param  array<string, string>  $description
     */
    public function __construct(
        public int $day,
        public MealTime $mealTime,
        public int $calories,
        public int $proteinG,
        public int $fatG,
        public int $carbsG,
        public array $name,
        public array $description,
    ) {}

    public static function fromCatalogueMeal(int $day, CatalogueMeal $meal): self
    {
        return new self(
            day: $day,
            mealTime: $meal->mealTime,
            calories: $meal->calories,
            proteinG: $meal->proteinG,
            fatG: $meal->fatG,
            carbsG: $meal->carbsG,
            name: $meal->name,
            description: $meal->description,
        );
    }
}
