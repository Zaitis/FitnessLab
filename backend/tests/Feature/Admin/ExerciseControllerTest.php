<?php

use App\Models\Exercise;
use App\Models\User;

function validExercisePayload(array $overrides = []): array
{
    return array_merge([
        'type' => 'strength',
        'location' => 'gym',
        'difficulty' => 'beginner',
        'muscle_group' => 'chest',
        'sets' => 3,
        'reps' => 10,
        'duration_minutes' => null,
        'name' => ['en' => 'Push-up', 'pl' => 'Pompka'],
        'instructions' => ['en' => 'Lower and push back up.', 'pl' => 'Opuść i wypchnij się z powrotem.'],
    ], $overrides);
}

it('rejects guests with 401', function () {
    $exercise = Exercise::factory()->create();

    $this->getJson('/api/admin/exercises')->assertStatus(401);
    $this->postJson('/api/admin/exercises', validExercisePayload())->assertStatus(401);
    $this->putJson("/api/admin/exercises/{$exercise->id}", validExercisePayload())->assertStatus(401);
    $this->deleteJson("/api/admin/exercises/{$exercise->id}")->assertStatus(401);
});

it('rejects an authenticated non-admin with 403', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->create();

    $this->actingAs($user)->getJson('/api/admin/exercises')->assertStatus(403);
    $this->actingAs($user)->postJson('/api/admin/exercises', validExercisePayload())->assertStatus(403);
    $this->actingAs($user)->putJson("/api/admin/exercises/{$exercise->id}", validExercisePayload())->assertStatus(403);
    $this->actingAs($user)->deleteJson("/api/admin/exercises/{$exercise->id}")->assertStatus(403);
});

it('lets an admin list the catalogue with every locale', function () {
    $admin = User::factory()->admin()->create();
    Exercise::factory()->create();

    $response = $this->actingAs($admin)->getJson('/api/admin/exercises')->assertOk();

    $response->assertJsonCount(1)
        ->assertJsonPath('0.name.en', fn ($name) => is_string($name))
        ->assertJsonPath('0.name.pl', fn ($name) => is_string($name));
});

it('lets an admin create a strength exercise', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->postJson('/api/admin/exercises', validExercisePayload())
        ->assertCreated();

    $response->assertJsonPath('name.en', 'Push-up')->assertJsonPath('name.pl', 'Pompka');
    $this->assertDatabaseHas('exercises', ['muscle_group' => 'chest', 'sets' => 3]);
});

it('lets an admin create a cardio exercise', function () {
    $admin = User::factory()->admin()->create();

    $payload = validExercisePayload([
        'type' => 'cardio',
        'muscle_group' => null,
        'sets' => null,
        'reps' => null,
        'duration_minutes' => 30,
        'name' => ['en' => 'Walking', 'pl' => 'Spacer'],
    ]);

    $this->actingAs($admin)->postJson('/api/admin/exercises', $payload)->assertCreated();

    $this->assertDatabaseHas('exercises', ['type' => 'cardio', 'duration_minutes' => 30]);
});

it('rejects a strength exercise missing muscle_group, sets, or reps with 422', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson('/api/admin/exercises', validExercisePayload(['muscle_group' => null]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('muscle_group');
});

it('rejects a cardio exercise that still carries strength-only fields with 422', function () {
    $admin = User::factory()->admin()->create();

    $payload = validExercisePayload([
        'type' => 'cardio',
        'duration_minutes' => 30,
        // muscle_group/sets/reps deliberately left as the strength defaults.
    ]);

    $this->actingAs($admin)
        ->postJson('/api/admin/exercises', $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['muscle_group', 'sets', 'reps']);
});

it('rejects a payload missing a required locale with 422', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson('/api/admin/exercises', validExercisePayload(['name' => ['en' => 'Push-up']]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('name.pl');
});

it('lets an admin update an exercise', function () {
    $admin = User::factory()->admin()->create();
    $exercise = Exercise::factory()->create();

    $this->actingAs($admin)
        ->putJson("/api/admin/exercises/{$exercise->id}", validExercisePayload(['sets' => 5]))
        ->assertOk()
        ->assertJsonPath('sets', 5);

    $this->assertDatabaseHas('exercises', ['id' => $exercise->id, 'sets' => 5]);
});

it('lets an admin delete an exercise', function () {
    $admin = User::factory()->admin()->create();
    $exercise = Exercise::factory()->create();

    $this->actingAs($admin)->deleteJson("/api/admin/exercises/{$exercise->id}")->assertNoContent();

    $this->assertDatabaseMissing('exercises', ['id' => $exercise->id]);
});
