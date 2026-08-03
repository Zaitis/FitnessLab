<?php

use App\Models\BmiMeasurement;
use App\Models\User;

it('rejects guests with 401', function () {
    $this->postJson('/api/measurements', [
        'weight_kg' => 70,
        'height_cm' => 175,
    ])->assertStatus(401);
});

it('persists a measurement for the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/api/measurements', [
        'weight_kg' => 70,
        'height_cm' => 175,
    ])
        ->assertCreated()
        ->assertJson([
            'value' => 22.9,
            'category' => 'normal',
        ]);

    expect(BmiMeasurement::where('user_id', $user->id)->count())->toBe(1);
});

it('rejects invalid input with 422', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/api/measurements', [
        'weight_kg' => -5,
        'height_cm' => 175,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('weight_kg');
});
