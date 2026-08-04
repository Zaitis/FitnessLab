<?php

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutPlan;

function seedMinimalCatalogue(): void
{
    $strengthGroups = ['chest', 'back', 'legs', 'shoulders'];

    foreach ($strengthGroups as $group) {
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
}

function validWorkoutPlanPayload(array $overrides = []): array
{
    return array_merge([
        'goal' => 'fat_loss',
        'experience_level' => 'beginner',
        'days_per_week' => 2,
        'location' => 'gym',
    ], $overrides);
}

it('rejects guests with 401', function () {
    $this->getJson('/api/workout-plans')->assertStatus(401);
    $this->postJson('/api/workout-plans', validWorkoutPlanPayload())->assertStatus(401);
    $this->getJson('/api/workout-plans/1')->assertStatus(401);
});

it('generates and persists a plan on the happy path', function () {
    seedMinimalCatalogue();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/workout-plans', validWorkoutPlanPayload())
        ->assertCreated();

    $response->assertJsonPath('goal', 'fat_loss')
        ->assertJsonPath('experience_level', 'beginner')
        ->assertJsonPath('days_per_week', 2)
        ->assertJsonCount(6, 'items') // 2 days x (1 cardio + 2 strength)
        ->assertJsonPath('disclaimer', config('disclaimer.standard.en'));

    $plan = WorkoutPlan::where('user_id', $user->id)->firstOrFail();
    expect($plan->generated_plan)->toHaveCount(6);

    // Every item has a UUID, and the response's item ids match the snapshot's.
    $snapshotIds = array_column($plan->generated_plan, 'id');
    $responseIds = array_column($response->json('items'), 'id');
    expect($responseIds)->toBe($snapshotIds);
});

it('rejects invalid input with 422', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/workout-plans', validWorkoutPlanPayload(['goal' => 'not-a-real-goal']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('goal');

    $this->actingAs($user)
        ->postJson('/api/workout-plans', validWorkoutPlanPayload(['days_per_week' => 7]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('days_per_week');
});

it('lists only the authenticated user\'s plans', function () {
    seedMinimalCatalogue();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)->postJson('/api/workout-plans', validWorkoutPlanPayload())->assertCreated();
    $this->actingAs($otherUser)->postJson('/api/workout-plans', validWorkoutPlanPayload())->assertCreated();

    $response = $this->actingAs($user)->getJson('/api/workout-plans')->assertOk();

    $response->assertJsonCount(1, 'data');
});

it('rejects a cross-user show request with 403', function () {
    seedMinimalCatalogue();
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $planId = $this->actingAs($owner)
        ->postJson('/api/workout-plans', validWorkoutPlanPayload())
        ->json('id');

    $this->actingAs($otherUser)->getJson("/api/workout-plans/{$planId}")->assertStatus(403);
    $this->actingAs($owner)->getJson("/api/workout-plans/{$planId}")->assertOk();
});
