<?php

arch('the domain knows nothing of the framework')
    ->expect('App\Domain')
    ->not->toUse('Illuminate');

arch('dependencies point inward')
    ->expect('App\Domain')
    ->not->toUse('App\Infrastructure');