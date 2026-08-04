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
 * A ~500 kcal/day deficit from estimated maintenance (roughly 0.5 kg/week),
 * with a higher protein share to help preserve muscle during the deficit.
 */
final class FatLossNutritionPlanStrategy implements NutritionPlanStrategy
{
    private const int DAYS = 7;

    private const int MIN_DAILY_CALORIES = 1200;

    private const int DEFICIT = 500;

    private const float PROTEIN_SHARE = 0.40;

    private const float FAT_SHARE = 0.30;

    private const float CARBS_SHARE = 0.30;

    public function __construct(private readonly MealTemplateCatalogue $catalogue) {}

    public function supports(NutritionPlanCriteria $criteria): bool
    {
        return $criteria->goal === Goal::FatLoss;
    }

    public function generate(NutritionPlanCriteria $criteria): GeneratedNutritionPlan
    {
        $dailyCalorieTarget = (int) round(max(
            self::MIN_DAILY_CALORIES,
            $criteria->estimatedTdee() - self::DEFICIT,
        ));

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
