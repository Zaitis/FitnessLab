<?php

use App\Models\BmiMeasurement;
use App\Models\MealTemplate;
use App\Models\NutritionPlan;
use App\Models\User;

function seedMinimalMealCatalogue(): void
{
    foreach (['breakfast', 'second_breakfast', 'lunch', 'afternoon_snack', 'dinner'] as $mealTime) {
        MealTemplate::create([
            'meal_time' => $mealTime,
            'calories' => 400,
            'protein_g' => 30,
            'fat_g' => 15,
            'carbs_g' => 40,
            'name' => ['en' => "Meal ({$mealTime})", 'pl' => "Posiłek ({$mealTime})"],
            'description' => ['en' => 'A meal.', 'pl' => 'Posiłek.'],
        ]);
    }
}

it('rejects guests with 401', function () {
    $this->getJson('/api/nutrition-plans')->assertStatus(401);
    $this->postJson('/api/nutrition-plans', ['goal' => 'fat_loss'])->assertStatus(401);
    $this->getJson('/api/nutrition-plans/1')->assertStatus(401);
});

it('generates and persists a plan on the happy path', function () {
    seedMinimalMealCatalogue();
    $user = User::factory()->create();
    BmiMeasurement::factory()->for($user)->create();

    $response = $this->actingAs($user)->postJson('/api/nutrition-plans', ['goal' => 'fat_loss'])
        ->assertCreated();

    $response->assertJsonPath('goal', 'fat_loss')
        ->assertJsonCount(35, 'items') // 7 days x 5 meal times
        ->assertJsonPath('disclaimer', config('disclaimer.standard.en'));

    expect($response->json('daily_calorie_target'))->toBeGreaterThan(0);
    expect($response->json('daily_protein_target_g'))->toBeGreaterThan(0);

    $plan = NutritionPlan::where('user_id', $user->id)->firstOrFail();
    expect($plan->generated_plan['items'])->toHaveCount(35);

    $snapshotIds = array_column($plan->generated_plan['items'], 'id');
    $responseIds = array_column($response->json('items'), 'id');
    expect($responseIds)->toBe($snapshotIds);
});

it('rejects generation with no recorded measurement', function () {
    seedMinimalMealCatalogue();
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/api/nutrition-plans', ['goal' => 'fat_loss'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('measurement');
});

it('rejects invalid input with 422', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/nutrition-plans', ['goal' => 'not-a-real-goal'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('goal');
});

it('lists only the authenticated user\'s plans', function () {
    seedMinimalMealCatalogue();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    BmiMeasurement::factory()->for($user)->create();
    BmiMeasurement::factory()->for($otherUser)->create();

    $this->actingAs($user)->postJson('/api/nutrition-plans', ['goal' => 'fat_loss'])->assertCreated();
    $this->actingAs($otherUser)->postJson('/api/nutrition-plans', ['goal' => 'fat_loss'])->assertCreated();

    $response = $this->actingAs($user)->getJson('/api/nutrition-plans')->assertOk();

    $response->assertJsonCount(1, 'data');
});

it('rejects a cross-user show request with 403', function () {
    seedMinimalMealCatalogue();
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    BmiMeasurement::factory()->for($owner)->create();

    $planId = $this->actingAs($owner)
        ->postJson('/api/nutrition-plans', ['goal' => 'fat_loss'])
        ->json('id');

    $this->actingAs($otherUser)->getJson("/api/nutrition-plans/{$planId}")->assertStatus(403);
    $this->actingAs($owner)->getJson("/api/nutrition-plans/{$planId}")->assertOk();
});
