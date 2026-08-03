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

it('rejects guests listing measurements with 401', function () {
    $this->getJson('/api/measurements')->assertStatus(401);
});

it('lists only the authenticated user\'s measurements, newest first', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    BmiMeasurement::factory()->for($user)->create(['measured_on' => '2026-01-01']);
    $newest = BmiMeasurement::factory()->for($user)->create(['measured_on' => '2026-06-01']);
    BmiMeasurement::factory()->for($otherUser)->create(['measured_on' => '2026-07-01']);

    $response = $this->actingAs($user)->getJson('/api/measurements')->assertOk();

    $response->assertJsonCount(2, 'data');
    expect($response->json('data.0.id'))->toBe($newest->id);
});

it('paginates measurements', function () {
    $user = User::factory()->create();
    BmiMeasurement::factory()->for($user)->count(25)->create();

    $response = $this->actingAs($user)->getJson('/api/measurements')->assertOk();

    $response->assertJsonCount(20, 'data');
    expect($response->json('total'))->toBe(25);
});
