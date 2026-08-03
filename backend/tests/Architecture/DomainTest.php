<?php

arch('the domain knows nothing of the framework')
    ->expect('App\Domain')
    ->not->toUse('Illuminate');

arch('dependencies point inward')
    ->expect('App\Domain')
    ->not->toUse('App\Infrastructure');

arch('value objects are immutable')
    ->expect([
        'App\Domain\Measurements\ValueObjects\Weight',
        'App\Domain\Measurements\ValueObjects\Height',
        'App\Domain\Measurements\ValueObjects\Bmi',
    ])
    ->toBeReadonly();
