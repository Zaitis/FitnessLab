<?php

use App\Models\User;

it('rejects guests with 401', function () {
    $this->patchJson('/api/user/locale', ['locale' => 'pl'])->assertStatus(401);
});

it('persists the authenticated user\'s locale', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)
        ->patchJson('/api/user/locale', ['locale' => 'pl'])
        ->assertOk()
        ->assertJson(['locale' => 'pl']);

    expect($user->fresh()->locale)->toBe('pl');
});

it('rejects an unsupported locale with 422', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson('/api/user/locale', ['locale' => 'fr'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('locale');
});
