<?php

namespace App\Application\Nutrition\Actions;

use App\Domain\Measurements\Enums\ActivityLevel;
use App\Domain\Measurements\Enums\Sex;
use App\Domain\Nutrition\Criteria\NutritionPlanCriteria;
use App\Domain\Nutrition\Enums\Goal;
use App\Domain\Nutrition\Strategies\NutritionPlanStrategy;
use App\Domain\Nutrition\ValueObjects\NutritionPlanItem;
use App\Models\NutritionPlan;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class GenerateNutritionPlanAction
{
    /**
     * @param  iterable<NutritionPlanStrategy>  $strategies
     */
    public function __construct(private readonly iterable $strategies) {}

    public function execute(User $user, Goal $goal): NutritionPlan
    {
        $measurement = $user->bmiMeasurements()
            ->whereNotNull('age')
            ->whereNotNull('sex')
            ->whereNotNull('activity_level')
            ->orderByDesc('measured_on')
            ->orderByDesc('id')
            ->first();

        if (! $measurement) {
            throw ValidationException::withMessages([
                'measurement' => 'Record a measurement with your age, sex, and activity level before generating a meal plan.',
            ]);
        }

        $criteria = new NutritionPlanCriteria(
            goal: $goal,
            weightKg: $measurement->weight_kg,
            heightCm: $measurement->height_cm,
            age: $measurement->age,
            sex: Sex::from($measurement->sex),
            activityLevel: ActivityLevel::from($measurement->activity_level),
        );

        $plan = $this->resolve($criteria)->generate($criteria);

        return NutritionPlan::create([
            'user_id' => $user->id,
            'goal' => $goal->value,
            'daily_calorie_target' => $plan->dailyCalorieTarget,
            'generated_plan' => [
                'items' => array_map($this->toSnapshotItem(...), $plan->items),
                'protein_g' => $plan->dailyProteinTargetG,
                'fat_g' => $plan->dailyFatTargetG,
                'carbs_g' => $plan->dailyCarbsTargetG,
            ],
        ]);
    }

    private function resolve(NutritionPlanCriteria $criteria): NutritionPlanStrategy
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($criteria)) {
                return $strategy;
            }
        }

        throw new RuntimeException('No nutrition plan strategy supports the given criteria.');
    }

    /**
     * @return array<string, mixed>
     */
    private function toSnapshotItem(NutritionPlanItem $item): array
    {
        return [
            'id' => (string) Str::uuid(),
            'day' => $item->day,
            'meal_time' => $item->mealTime->value,
            'calories' => $item->calories,
            'protein_g' => $item->proteinG,
            'fat_g' => $item->fatG,
            'carbs_g' => $item->carbsG,
            'name' => $item->name,
            'description' => $item->description,
        ];
    }
}
