<?php

namespace App\Domain\Nutrition\Contracts;

use App\Domain\Nutrition\ValueObjects\MealCollection;
use App\Domain\Nutrition\ValueObjects\MealFilter;

interface MealTemplateCatalogue
{
    public function matching(MealFilter $filter): MealCollection;
}
