<?php

namespace App\Domain\Nutrition\Strategies;

use App\Domain\Nutrition\Contracts\MealTemplateCatalogue;
use App\Domain\Nutrition\Criteria\NutritionPlanCriteria;
use App\Domain\Nutrition\Enums\Goal;
use App\Domain\Nutrition\Enums\MealTime;
use App\Domain\Nutrition\ValueObjects\GeneratedNutritionPlan;
use App\Domain\Nutrition\ValueObjects\MealFilter;
use App\Domain\Nutrition\ValueObjects\NutritionPlanItem;

/**
 * A ~300 kcal/day surplus over estimated maintenance, with more carbs to
 * fuel training and support the surplus.
 */
final class MuscleGainNutritionPlanStrategy implements NutritionPlanStrategy
{
    private const int DAYS = 7;

    private const int SURPLUS = 300;

    private const float PROTEIN_SHARE = 0.30;

    private const float FAT_SHARE = 0.25;

    private const float CARBS_SHARE = 0.45;

    public function __construct(private readonly MealTemplateCatalogue $catalogue) {}

    public function supports(NutritionPlanCriteria $criteria): bool
    {
        return $criteria->goal === Goal::MuscleGain;
    }

    public function generate(NutritionPlanCriteria $criteria): GeneratedNutritionPlan
    {
        $dailyCalorieTarget = (int) round($criteria->estimatedTdee() + self::SURPLUS);

        $items = [];

        for ($day = 1; $day <= self::DAYS; $day++) {
            foreach (MealTime::cases() as $mealTime) {
                $target = (int) round($dailyCalorieTarget * $mealTime->calorieShare());

                $meals = $this->catalogue->matching(new MealFilter($mealTime))
                    ->sortedByProximityTo($target);

                if ($meal = $meals->nth($day - 1)) {
                    $items[] = NutritionPlanItem::fromCatalogueMeal($day, $meal);
                }
            }
        }

        return new GeneratedNutritionPlan(
            items: $items,
            dailyCalorieTarget: $dailyCalorieTarget,
            dailyProteinTargetG: (int) round($dailyCalorieTarget * self::PROTEIN_SHARE / 4),
            dailyFatTargetG: (int) round($dailyCalorieTarget * self::FAT_SHARE / 9),
            dailyCarbsTargetG: (int) round($dailyCalorieTarget * self::CARBS_SHARE / 4),
        );
    }
}
