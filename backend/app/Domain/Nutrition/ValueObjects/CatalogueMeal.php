<?php

namespace App\Domain\Nutrition\ValueObjects;

use App\Domain\Nutrition\Enums\MealTime;

/**
 * One catalogue row, translated into every supported locale up front — the
 * same reasoning as App\Domain\Workouts\ValueObjects\CatalogueExercise.
 */
final readonly class CatalogueMeal
{
    /**
     * @param  array<string, string>  $name  locale => name
     * @param  array<string, string>  $description  locale => description
     */
    public function __construct(
        public int $id,
        public MealTime $mealTime,
        public int $calories,
        public int $proteinG,
        public int $fatG,
        public int $carbsG,
        public array $name,
        public array $description,
    ) {}
}
