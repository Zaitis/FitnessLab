<?php

namespace Tests\Support;

use App\Domain\Nutrition\Contracts\MealTemplateCatalogue;
use App\Domain\Nutrition\ValueObjects\CatalogueMeal;
use App\Domain\Nutrition\ValueObjects\MealCollection;
use App\Domain\Nutrition\ValueObjects\MealFilter;

final class InMemoryMealTemplateCatalogue implements MealTemplateCatalogue
{
    /**
     * @param  list<CatalogueMeal>  $meals
     */
    public function __construct(private readonly array $meals) {}

    public function matching(MealFilter $filter): MealCollection
    {
        return new MealCollection(array_values(array_filter(
            $this->meals,
            fn (CatalogueMeal $meal) => $meal->mealTime === $filter->mealTime,
        )));
    }
}
