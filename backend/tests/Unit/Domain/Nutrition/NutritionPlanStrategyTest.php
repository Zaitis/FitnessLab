<?php

use App\Domain\Measurements\Enums\ActivityLevel;
use App\Domain\Measurements\Enums\Sex;
use App\Domain\Nutrition\Criteria\NutritionPlanCriteria;
use App\Domain\Nutrition\Enums\Goal;
use App\Domain\Nutrition\Strategies\FatLossNutritionPlanStrategy;
use App\Domain\Nutrition\Strategies\MaintenanceNutritionPlanStrategy;
use App\Domain\Nutrition\Strategies\MuscleGainNutritionPlanStrategy;
use App\Domain\Nutrition\Strategies\NutritionPlanStrategy;
use Tests\Support\NutritionFixtures;

function nutritionStrategyFor(Goal $goal): NutritionPlanStrategy
{
    $catalogue = NutritionFixtures::catalogue();

    return match ($goal) {
        Goal::FatLoss => new FatLossNutritionPlanStrategy($catalogue),
        Goal::MuscleGain => new MuscleGainNutritionPlanStrategy($catalogue),
        Goal::Maintenance => new MaintenanceNutritionPlanStrategy($catalogue),
    };
}

function criteriaFor(Goal $goal, array $profile): NutritionPlanCriteria
{
    return new NutritionPlanCriteria(
        goal: $goal,
        weightKg: $profile['weightKg'],
        heightCm: $profile['heightCm'],
        age: $profile['age'],
        sex: $profile['sex'],
        activityLevel: $profile['activityLevel'],
    );
}

dataset('nutritionGoals', Goal::cases());

dataset('profiles', [
    'active young male' => [['weightKg' => 82.0, 'heightCm' => 180.0, 'age' => 28, 'sex' => Sex::Male, 'activityLevel' => ActivityLevel::Active]],
    'sedentary older female' => [['weightKg' => 58.0, 'heightCm' => 160.0, 'age' => 55, 'sex' => Sex::Female, 'activityLevel' => ActivityLevel::Sedentary]],
    'moderate middle-aged male' => [['weightKg' => 95.0, 'heightCm' => 175.0, 'age' => 40, 'sex' => Sex::Male, 'activityLevel' => ActivityLevel::Moderate]],
]);

test('supports() matches exactly the goal it is built for', function (Goal $goal) {
    $strategy = nutritionStrategyFor($goal);

    foreach (Goal::cases() as $candidate) {
        $criteria = criteriaFor($candidate, ['weightKg' => 80.0, 'heightCm' => 175.0, 'age' => 30, 'sex' => Sex::Male, 'activityLevel' => ActivityLevel::Moderate]);

        expect($strategy->supports($criteria))->toBe($candidate === $goal);
    }
})->with('nutritionGoals');

test('generates a 7-day, 5-meal-a-day plan for every goal x profile combination', function (Goal $goal, array $profile) {
    $plan = nutritionStrategyFor($goal)->generate(criteriaFor($goal, $profile));

    expect($plan->items)->toHaveCount(35)
        ->and($plan->dailyCalorieTarget)->toBeGreaterThan(0);

    $days = array_unique(array_map(fn ($item) => $item->day, $plan->items));
    expect($days)->toHaveCount(7);
})->with('nutritionGoals')->with('profiles');

test('every plan item carries text in every supported locale', function (Goal $goal, array $profile) {
    $plan = nutritionStrategyFor($goal)->generate(criteriaFor($goal, $profile));

    foreach (['en', 'pl'] as $locale) {
        foreach ($plan->items as $item) {
            expect($item->name)->toHaveKey($locale)
                ->and($item->description)->toHaveKey($locale);
        }
    }
})->with('nutritionGoals')->with('profiles');

test('generation is deterministic for identical input', function (Goal $goal, array $profile) {
    $criteria = criteriaFor($goal, $profile);

    $first = nutritionStrategyFor($goal)->generate($criteria);
    $second = nutritionStrategyFor($goal)->generate($criteria);

    $normalise = fn ($plan) => [
        $plan->dailyCalorieTarget,
        array_map(fn ($item) => [$item->day, $item->mealTime->value, $item->name['en']], $plan->items),
    ];

    expect($normalise($first))->toBe($normalise($second));
})->with('nutritionGoals')->with('profiles');

test('the daily calorie target respects goal direction for the same profile', function (array $profile) {
    $fatLoss = nutritionStrategyFor(Goal::FatLoss)->generate(criteriaFor(Goal::FatLoss, $profile));
    $maintenance = nutritionStrategyFor(Goal::Maintenance)->generate(criteriaFor(Goal::Maintenance, $profile));
    $muscleGain = nutritionStrategyFor(Goal::MuscleGain)->generate(criteriaFor(Goal::MuscleGain, $profile));

    expect($fatLoss->dailyCalorieTarget)->toBeLessThan($maintenance->dailyCalorieTarget)
        ->and($maintenance->dailyCalorieTarget)->toBeLessThan($muscleGain->dailyCalorieTarget);
})->with('profiles');

test('the calorie target never drops below the safety floor for a very light profile', function () {
    $tinyProfile = ['weightKg' => 42.0, 'heightCm' => 150.0, 'age' => 70, 'sex' => Sex::Female, 'activityLevel' => ActivityLevel::Sedentary];

    $fatLoss = nutritionStrategyFor(Goal::FatLoss)->generate(criteriaFor(Goal::FatLoss, $tinyProfile));

    expect($fatLoss->dailyCalorieTarget)->toBeGreaterThanOrEqual(1200);
});
