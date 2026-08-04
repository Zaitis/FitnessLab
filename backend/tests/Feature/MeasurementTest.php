<?php

use App\Models\BmiMeasurement;
use App\Models\User;

function validMeasurementPayload(array $overrides = []): array
{
    return array_merge([
        'weight_kg' => 70,
        'height_cm' => 175,
        'age' => 30,
        'sex' => 'male',
        'activity_level' => 'moderate',
    ], $overrides);
}

it('rejects guests with 401', function () {
    $this->postJson('/api/measurements', validMeasurementPayload())->assertStatus(401);
});

it('persists a measurement for the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/api/measurements', validMeasurementPayload())
        ->assertCreated()
        ->assertJson([
            'value' => 22.9,
            'category' => 'normal',
            'age' => 30,
            'sex' => 'male',
            'activity_level' => 'moderate',
        ]);

    expect(BmiMeasurement::where('user_id', $user->id)->count())->toBe(1);
});

it('rejects invalid input with 422', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/api/measurements', validMeasurementPayload(['weight_kg' => -5]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('weight_kg');
});

it('rejects an invalid sex or activity_level with 422', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/measurements', validMeasurementPayload(['sex' => 'not-a-real-sex']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('sex');

    $this->actingAs($user)
        ->postJson('/api/measurements', validMeasurementPayload(['activity_level' => 'not-a-real-level']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('activity_level');
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
