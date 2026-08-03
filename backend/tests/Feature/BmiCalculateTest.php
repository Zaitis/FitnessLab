<?php

use App\Domain\Measurements\ValueObjects\BmiCategory;
use Illuminate\Support\Facades\DB;

it('calculates bmi for valid input', function () {
    $this->postJson('/api/bmi/calculate', [
        'weight_kg' => 70,
        'height_cm' => 175,
    ])
        ->assertOk()
        ->assertJson([
            'value' => 22.9,
            'category' => BmiCategory::Normal->value,
        ]);
});

it('rejects invalid input with 422', function () {
    $this->postJson('/api/bmi/calculate', [
        'weight_kg' => -5,
        'height_cm' => 175,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('weight_kg');
});

it('persists nothing', function () {
    DB::enableQueryLog();

    $this->postJson('/api/bmi/calculate', [
        'weight_kg' => 70,
        'height_cm' => 175,
    ])->assertOk();

    $writes = collect(DB::getQueryLog())->filter(
        fn (array $query) => str_starts_with(strtolower((string) $query['query']), 'insert'),
    );

    expect($writes)->toBeEmpty();
});

it('returns 429 once the rate limit is exceeded', function () {
    for ($i = 0; $i < 10; $i++) {
        $this->postJson('/api/bmi/calculate', [
            'weight_kg' => 70,
            'height_cm' => 175,
        ])->assertOk();
    }

    $this->postJson('/api/bmi/calculate', [
        'weight_kg' => 70,
        'height_cm' => 175,
    ])->assertStatus(429);
});
