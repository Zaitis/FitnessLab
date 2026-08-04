<?php

namespace App\Domain\Nutrition\ValueObjects;

use App\Domain\Nutrition\Enums\MealTime;

final readonly class MealFilter
{
    public function __construct(public MealTime $mealTime) {}
}
