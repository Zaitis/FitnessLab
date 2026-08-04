<?php

arch('the domain knows nothing of the framework')
    ->expect('App\Domain')
    ->not->toUse('Illuminate');

arch('dependencies point inward')
    ->expect('App\Domain')
    ->not->toUse('App\Infrastructure');

arch('workouts does not reference other modules')
    ->expect('App\Domain\Workouts')
    ->not->toUse(['App\Domain\Measurements', 'App\Domain\Disclaimers']);

// Nutrition is the one exception that legitimately reuses another module's
// types: Sex and ActivityLevel are plain enums owned by Measurements (where
// they're captured), not an Eloquent model — reusing them isn't the
// cross-module coupling this rule guards against. What actually matters is
// that Nutrition stays its own parallel hierarchy rather than depending on
// Workouts (docs/DESIGN-PATTERNS.md §1) or Disclaimers.
arch('nutrition does not reference workouts or disclaimers')
    ->expect('App\Domain\Nutrition')
    ->not->toUse(['App\Domain\Workouts', 'App\Domain\Disclaimers']);

arch('value objects are immutable')
    ->expect([
        'App\Domain\Measurements\ValueObjects\Weight',
        'App\Domain\Measurements\ValueObjects\Height',
        'App\Domain\Measurements\ValueObjects\Bmi',
    ])
    ->toBeReadonly();
