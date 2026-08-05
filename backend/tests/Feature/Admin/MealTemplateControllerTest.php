<?php

use App\Models\MealTemplate;
use App\Models\User;

function validMealTemplatePayload(array $overrides = []): array
{
    return array_merge([
        'meal_time' => 'breakfast',
        'calories' => 400,
        'protein_g' => 20,
        'fat_g' => 15,
        'carbs_g' => 45,
        'name' => ['en' => 'Oatmeal with Banana', 'pl' => 'Owsianka z bananem'],
        'description' => ['en' => 'Rolled oats with banana.', 'pl' => 'Płatki owsiane z bananem.'],
    ], $overrides);
}

it('rejects guests with 401', function () {
    $mealTemplate = MealTemplate::factory()->create();

    $this->getJson('/api/admin/meal-templates')->assertStatus(401);
    $this->postJson('/api/admin/meal-templates', validMealTemplatePayload())->assertStatus(401);
    $this->putJson("/api/admin/meal-templates/{$mealTemplate->id}", validMealTemplatePayload())
        ->assertStatus(401);
    $this->deleteJson("/api/admin/meal-templates/{$mealTemplate->id}")->assertStatus(401);
});

it('rejects an authenticated non-admin with 403', function () {
    $user = User::factory()->create();
    $mealTemplate = MealTemplate::factory()->create();

    $this->actingAs($user)->getJson('/api/admin/meal-templates')->assertStatus(403);
    $this->actingAs($user)->postJson('/api/admin/meal-templates', validMealTemplatePayload())
        ->assertStatus(403);
    $this->actingAs($user)
        ->putJson("/api/admin/meal-templates/{$mealTemplate->id}", validMealTemplatePayload())
        ->assertStatus(403);
    $this->actingAs($user)->deleteJson("/api/admin/meal-templates/{$mealTemplate->id}")
        ->assertStatus(403);
});

it('lets an admin list the catalogue with every locale', function () {
    $admin = User::factory()->admin()->create();
    MealTemplate::factory()->create();

    $response = $this->actingAs($admin)->getJson('/api/admin/meal-templates')->assertOk();

    $response->assertJsonCount(1)
        ->assertJsonPath('0.name.en', fn ($name) => is_string($name))
        ->assertJsonPath('0.name.pl', fn ($name) => is_string($name));
});

it('lets an admin create a meal template', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->postJson('/api/admin/meal-templates', validMealTemplatePayload())
        ->assertCreated();

    $response->assertJsonPath('name.en', 'Oatmeal with Banana')
        ->assertJsonPath('name.pl', 'Owsianka z bananem');
    $this->assertDatabaseHas('meal_templates', ['meal_time' => 'breakfast', 'calories' => 400]);
});

it('rejects invalid input with 422', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson('/api/admin/meal-templates', validMealTemplatePayload(['meal_time' => 'brunch']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('meal_time');

    $this->actingAs($admin)
        ->postJson('/api/admin/meal-templates', validMealTemplatePayload(['calories' => -1]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('calories');
});

it('rejects a payload missing a required locale with 422', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson(
            '/api/admin/meal-templates',
            validMealTemplatePayload(['name' => ['en' => 'Oatmeal with Banana']]),
        )
        ->assertStatus(422)
        ->assertJsonValidationErrors('name.pl');
});

it('lets an admin update a meal template', function () {
    $admin = User::factory()->admin()->create();
    $mealTemplate = MealTemplate::factory()->create();

    $this->actingAs($admin)
        ->putJson(
            "/api/admin/meal-templates/{$mealTemplate->id}",
            validMealTemplatePayload(['calories' => 550]),
        )
        ->assertOk()
        ->assertJsonPath('calories', 550);

    $this->assertDatabaseHas('meal_templates', ['id' => $mealTemplate->id, 'calories' => 550]);
});

it('lets an admin delete a meal template', function () {
    $admin = User::factory()->admin()->create();
    $mealTemplate = MealTemplate::factory()->create();

    $this->actingAs($admin)->deleteJson("/api/admin/meal-templates/{$mealTemplate->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('meal_templates', ['id' => $mealTemplate->id]);
});
