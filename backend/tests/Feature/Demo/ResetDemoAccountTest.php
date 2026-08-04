<?php

use App\Application\Demo\Actions\ResetDemoAccountAction;
use App\Models\AdherenceEntry;
use App\Models\BmiMeasurement;
use App\Models\Exercise;
use App\Models\MealTemplate;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Str;

function seedCatalogueForDemo(): void
{
    foreach (['chest', 'back', 'legs', 'shoulders'] as $group) {
        Exercise::create([
            'type' => 'strength',
            'location' => 'gym',
            'difficulty' => 'beginner',
            'muscle_group' => $group,
            'sets' => 3,
            'reps' => 10,
            'duration_minutes' => null,
            'name' => ['en' => "Exercise ({$group})", 'pl' => "Ćwiczenie ({$group})"],
            'instructions' => ['en' => 'Do the exercise.', 'pl' => 'Wykonaj ćwiczenie.'],
        ]);
    }

    Exercise::create([
        'type' => 'cardio',
        'location' => 'outdoor',
        'difficulty' => 'beginner',
        'muscle_group' => null,
        'sets' => null,
        'reps' => null,
        'duration_minutes' => 30,
        'name' => ['en' => 'Walking', 'pl' => 'Spacer'],
        'instructions' => ['en' => 'Walk briskly.', 'pl' => 'Idź energicznym krokiem.'],
    ]);

    foreach (['breakfast', 'second_breakfast', 'lunch', 'afternoon_snack', 'dinner'] as $mealTime) {
        MealTemplate::create([
            'meal_time' => $mealTime,
            'calories' => 400,
            'protein_g' => 30,
            'fat_g' => 15,
            'carbs_g' => 40,
            'name' => ['en' => "Meal ({$mealTime})", 'pl' => "Posiłek ({$mealTime})"],
            'description' => ['en' => 'A meal.', 'pl' => 'Posiłek.'],
        ]);
    }
}

it('creates a populated demo account', function () {
    seedCatalogueForDemo();

    app(ResetDemoAccountAction::class)->execute();

    $user = User::where('email', config('demo.email'))->firstOrFail();

    expect($user->is_demo)->toBeTrue()
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->bmiMeasurements()->count())->toBe(4)
        ->and($user->workoutPlans()->count())->toBe(1)
        ->and($user->nutritionPlans()->count())->toBe(1)
        ->and($user->adherenceEntries()->count())->toBeGreaterThan(0);

    // Today has entries, so a visitor opening the calendar sees something
    // checked off immediately without navigating away from the default view.
    expect($user->adherenceEntries()->where('entry_date', now()->toDateString())->count())
        ->toBeGreaterThan(0);
});

it('is idempotent: running it twice leaves exactly one demo account', function () {
    seedCatalogueForDemo();

    app(ResetDemoAccountAction::class)->execute();
    app(ResetDemoAccountAction::class)->execute();

    expect(User::where('email', config('demo.email'))->count())->toBe(1);
});

it('restores the seeded state after visitor edits mutate it', function () {
    seedCatalogueForDemo();

    app(ResetDemoAccountAction::class)->execute();
    $user = User::where('email', config('demo.email'))->firstOrFail();

    // Simulate a visitor's session: an extra measurement, a deleted plan,
    // a stray adherence entry that doesn't belong to any real plan item.
    BmiMeasurement::factory()->for($user)->create();
    $user->nutritionPlans()->delete();
    AdherenceEntry::create([
        'user_id' => $user->id,
        'entry_date' => now()->toDateString(),
        'plan_type' => 'workout',
        'plan_id' => 999999,
        'plan_item_id' => (string) Str::uuid(),
        'completed_at' => now(),
    ]);

    app(ResetDemoAccountAction::class)->execute();
    $user->refresh();

    expect($user->bmiMeasurements()->count())->toBe(4)
        ->and($user->nutritionPlans()->count())->toBe(1)
        ->and($user->workoutPlans()->count())->toBe(1)
        ->and(AdherenceEntry::where('plan_id', 999999)->exists())->toBeFalse();
});

it('runs via the scheduled console command', function () {
    seedCatalogueForDemo();

    $this->artisan('demo:reset')->assertSuccessful();

    expect(User::where('email', config('demo.email'))->exists())->toBeTrue();
});

it('is registered on the daily schedule', function () {
    $schedule = app(Schedule::class);

    $found = collect($schedule->events())
        ->contains(fn ($event) => str_contains($event->command ?? '', 'demo:reset'));

    expect($found)->toBeTrue();
});
