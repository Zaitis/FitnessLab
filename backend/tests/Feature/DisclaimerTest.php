<?php

it('returns the default locale disclaimer', function () {
    $this->getJson('/api/disclaimer')
        ->assertOk()
        ->assertJson([
            'short' => config('disclaimer.short.en'),
            'standard' => config('disclaimer.standard.en'),
            'extended' => config('disclaimer.extended.en'),
        ]);
});

it('returns the requested locale disclaimer', function () {
    $this->getJson('/api/disclaimer?locale=pl')
        ->assertOk()
        ->assertJson([
            'short' => config('disclaimer.short.pl'),
        ]);
});

it('falls back to the default locale for an unsupported locale', function () {
    $this->getJson('/api/disclaimer?locale=de')
        ->assertOk()
        ->assertJson([
            'short' => config('disclaimer.short.en'),
        ]);
});
