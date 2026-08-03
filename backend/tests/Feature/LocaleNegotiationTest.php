<?php

use App\Models\User;

it('uses accept-language to select the validation message locale', function () {
    $english = $this->postJson('/api/bmi/calculate', ['weight_kg' => -5, 'height_cm' => 175])
        ->assertStatus(422)
        ->json('errors.weight_kg.0');

    $polish = $this->withHeader('Accept-Language', 'pl')
        ->postJson('/api/bmi/calculate', ['weight_kg' => -5, 'height_cm' => 175])
        ->assertStatus(422)
        ->json('errors.weight_kg.0');

    expect($polish)->not->toBe($english);
});

it("lets an authenticated user's stored locale take precedence over accept-language", function () {
    $user = User::factory()->create(['locale' => 'pl']);

    $polishReference = $this->withHeader('Accept-Language', 'pl')
        ->postJson('/api/bmi/calculate', ['weight_kg' => -5, 'height_cm' => 175])
        ->json('errors.weight_kg.0');

    $actual = $this->actingAs($user)
        ->withHeader('Accept-Language', 'en')
        ->postJson('/api/bmi/calculate', ['weight_kg' => -5, 'height_cm' => 175])
        ->assertStatus(422)
        ->json('errors.weight_kg.0');

    expect($actual)->toBe($polishReference);
});
