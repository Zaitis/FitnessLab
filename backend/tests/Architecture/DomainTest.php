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

arch('value objects are immutable')
    ->expect([
        'App\Domain\Measurements\ValueObjects\Weight',
        'App\Domain\Measurements\ValueObjects\Height',
        'App\Domain\Measurements\ValueObjects\Bmi',
    ])
    ->toBeReadonly();
