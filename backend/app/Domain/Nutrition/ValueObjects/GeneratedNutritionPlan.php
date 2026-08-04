<?php

namespace App\Domain\Nutrition\ValueObjects;

final readonly class GeneratedNutritionPlan
{
    /**
     * @param  list<NutritionPlanItem>  $items
     */
    public function __construct(
        public array $items,
        public int $dailyCalorieTarget,
        public int $dailyProteinTargetG,
        public int $dailyFatTargetG,
        public int $dailyCarbsTargetG,
    ) {}
}
