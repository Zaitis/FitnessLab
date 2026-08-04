<?php

namespace App\Domain\Nutrition\Strategies;

use App\Domain\Nutrition\Criteria\NutritionPlanCriteria;
use App\Domain\Nutrition\ValueObjects\GeneratedNutritionPlan;

interface NutritionPlanStrategy
{
    public function supports(NutritionPlanCriteria $criteria): bool;

    public function generate(NutritionPlanCriteria $criteria): GeneratedNutritionPlan;
}
