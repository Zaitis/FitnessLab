<?php

use App\Models\AdherenceEntry;
use App\Models\Exercise;
use App\Models\User;

function seedMinimalWorkoutCatalogue(): void
{
    Exercise::create([
        'type' => 'strength',
        'location' => 'gym',
        'difficulty' => 'beginner',
        'muscle_group' => 'chest',
        'sets' => 3,
        'reps' => 10,
        'duration_minutes' => null,
        'name' => ['en' => 'Push-up', 'pl' => 'Pompka'],
        'instructions' => ['en' => 'Do the exercise.', 'pl' => 'Wykonaj ćwiczenie.'],
    ]);

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

function createWorkoutPlanFor(User $user): array
{
    seedMinimalWorkoutCatalogue();

    return test()->actingAs($user)
        ->postJson('/api/workout-plans', [
            'goal' => 'fat_loss',
            'experience_level' => 'beginner',
            'days_per_week' => 2,
            'location' => 'gym',
        ])
        ->assertCreated()
        ->json();
}

function adherencePayload(array $plan, array $overrides = []): array
{
    return array_merge([
        'entry_date' => now()->toDateString(),
        'plan_type' => 'workout',
        'plan_id' => $plan['id'],
        'plan_item_id' => $plan['items'][0]['id'],
    ], $overrides);
}

it('rejects guests with 401', function () {
    $this->getJson('/api/adherence?month='.now()->format('Y-m'))->assertStatus(401);
    $this->postJson('/api/adherence', [])->assertStatus(401);
    $this->deleteJson('/api/adherence', [])->assertStatus(401);
});

it('checks off an item on the happy path and lists it for the month', function () {
    $user = User::factory()->create();
    $plan = createWorkoutPlanFor($user);
    $payload = adherencePayload($plan);

    $this->actingAs($user)->postJson('/api/adherence', $payload)
        ->assertOk()
        ->assertJsonPath('checked', true);

    expect(AdherenceEntry::where('user_id', $user->id)->count())->toBe(1);

    $this->actingAs($user)->getJson('/api/adherence?month='.now()->format('Y-m'))
        ->assertOk()
        ->assertJsonFragment([
            'entry_date' => $payload['entry_date'],
            'plan_type' => 'workout',
            'plan_id' => $plan['id'],
            'plan_item_id' => $payload['plan_item_id'],
        ]);
});

it('rejects invalid input with 422', function () {
    $user = User::factory()->create();
    $plan = createWorkoutPlanFor($user);

    $this->actingAs($user)
        ->postJson('/api/adherence', adherencePayload($plan, ['plan_type' => 'not-a-real-type']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('plan_type');

    $this->actingAs($user)
        ->postJson('/api/adherence', adherencePayload($plan, ['plan_item_id' => 'not-a-uuid']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('plan_item_id');
});

it('holds the unique constraint when checking the same item twice', function () {
    $user = User::factory()->create();
    $plan = createWorkoutPlanFor($user);
    $payload = adherencePayload($plan);

    $this->actingAs($user)->postJson('/api/adherence', $payload)->assertOk();
    $this->actingAs($user)->postJson('/api/adherence', $payload)->assertOk();

    expect(AdherenceEntry::where('user_id', $user->id)->count())->toBe(1);
});

it('returns to the original state after checking and unchecking', function () {
    $user = User::factory()->create();
    $plan = createWorkoutPlanFor($user);
    $payload = adherencePayload($plan);

    $this->actingAs($user)->postJson('/api/adherence', $payload)->assertOk();
    expect(AdherenceEntry::where('user_id', $user->id)->count())->toBe(1);

    $this->actingAs($user)->deleteJson('/api/adherence', $payload)
        ->assertOk()
        ->assertJsonPath('checked', false);

    expect(AdherenceEntry::where('user_id', $user->id)->count())->toBe(0);
});

it('rejects an item id that does not belong to the given plan', function () {
    $user = User::factory()->create();
    $plan = createWorkoutPlanFor($user);

    $this->actingAs($user)
        ->postJson('/api/adherence', adherencePayload($plan, [
            'plan_item_id' => '3f2504e0-4f89-11d3-9a0c-0305e82c3301',
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('plan_item_id');

    expect(AdherenceEntry::count())->toBe(0);
});

it('rejects checking off another user\'s plan with 403', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $plan = createWorkoutPlanFor($owner);
    $payload = adherencePayload($plan);

    $this->actingAs($intruder)->postJson('/api/adherence', $payload)->assertStatus(403);

    expect(AdherenceEntry::count())->toBe(0);
});
