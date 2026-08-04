<?php

use App\Domain\Workouts\Criteria\WorkoutPlanCriteria;
use App\Domain\Workouts\Enums\ExerciseLocation;
use App\Domain\Workouts\Enums\ExerciseType;
use App\Domain\Workouts\Enums\ExperienceLevel;
use App\Domain\Workouts\Enums\Goal;
use App\Domain\Workouts\Strategies\FatLossWorkoutPlanStrategy;
use App\Domain\Workouts\Strategies\MaintenanceWorkoutPlanStrategy;
use App\Domain\Workouts\Strategies\MuscleGainWorkoutPlanStrategy;
use App\Domain\Workouts\Strategies\WorkoutPlanStrategy;
use Tests\Support\WorkoutFixtures;

function strategyFor(Goal $goal): WorkoutPlanStrategy
{
    $catalogue = WorkoutFixtures::catalogue();

    return match ($goal) {
        Goal::FatLoss => new FatLossWorkoutPlanStrategy($catalogue),
        Goal::MuscleGain => new MuscleGainWorkoutPlanStrategy($catalogue),
        Goal::Maintenance => new MaintenanceWorkoutPlanStrategy($catalogue),
    };
}

dataset('goals', Goal::cases());
dataset('levels', ExperienceLevel::cases());
dataset('daysPerWeek', [1, 3, 6]);

test('supports() matches exactly the goal it is built for', function (Goal $goal) {
    $strategy = strategyFor($goal);

    foreach (Goal::cases() as $candidate) {
        $criteria = new WorkoutPlanCriteria($candidate, ExperienceLevel::Beginner, 3, ExerciseLocation::Gym);

        expect($strategy->supports($criteria))->toBe($candidate === $goal);
    }
})->with('goals');

test('generates a non-empty plan for every goal x level x days combination', function (Goal $goal, ExperienceLevel $level, int $days) {
    $criteria = new WorkoutPlanCriteria($goal, $level, $days, ExerciseLocation::Gym);
    $plan = strategyFor($goal)->generate($criteria);

    expect($plan->items)->not->toBeEmpty();

    $daysCovered = array_unique(array_map(fn ($item) => $item->day, $plan->items));
    expect(max($daysCovered))->toBeLessThanOrEqual($days)
        ->and(min($daysCovered))->toBeGreaterThanOrEqual(1);
})->with('goals')->with('levels')->with('daysPerWeek');

test('every plan item carries text in every supported locale', function (Goal $goal, ExperienceLevel $level, int $days) {
    $criteria = new WorkoutPlanCriteria($goal, $level, $days, ExerciseLocation::Gym);
    $plan = strategyFor($goal)->generate($criteria);

    // Hardcoded rather than read from config('supported_locales') — this is
    // a pure unit test with no Laravel container, by design (see
    // docs/DESIGN-PATTERNS.md §1's "no HTTP, no database, and no container").
    foreach (['en', 'pl'] as $locale) {
        foreach ($plan->items as $item) {
            expect($item->name)->toHaveKey($locale)
                ->and($item->instructions)->toHaveKey($locale);
        }
    }
})->with('goals')->with('levels')->with('daysPerWeek');

test('generation is deterministic for identical input', function (Goal $goal, ExperienceLevel $level, int $days) {
    $criteria = new WorkoutPlanCriteria($goal, $level, $days, ExerciseLocation::Gym);

    $first = strategyFor($goal)->generate($criteria);
    $second = strategyFor($goal)->generate($criteria);

    $normalise = fn ($plan) => array_map(
        fn ($item) => [$item->day, $item->type->value, $item->name['en']],
        $plan->items,
    );

    expect($normalise($first))->toBe($normalise($second));
})->with('goals')->with('levels')->with('daysPerWeek');

test('fat loss always includes cardio', function (ExperienceLevel $level, int $days) {
    $criteria = new WorkoutPlanCriteria(Goal::FatLoss, $level, $days, ExerciseLocation::Gym);
    $plan = strategyFor(Goal::FatLoss)->generate($criteria);

    $cardioItems = array_filter($plan->items, fn ($item) => $item->type === ExerciseType::Cardio);

    expect($cardioItems)->not->toBeEmpty();
})->with('levels')->with('daysPerWeek');

test('muscle gain never includes cardio', function (ExperienceLevel $level, int $days) {
    $criteria = new WorkoutPlanCriteria(Goal::MuscleGain, $level, $days, ExerciseLocation::Gym);
    $plan = strategyFor(Goal::MuscleGain)->generate($criteria);

    $cardioItems = array_filter($plan->items, fn ($item) => $item->type === ExerciseType::Cardio);

    expect($cardioItems)->toBeEmpty();
})->with('levels')->with('daysPerWeek');
