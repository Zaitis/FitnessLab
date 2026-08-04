<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;

it('does not reveal whether an email has an account', function () {
    Notification::fake();

    $user = User::factory()->create();

    $known = $this->postJson('/api/forgot-password', ['email' => $user->email]);
    $unknown = $this->postJson('/api/forgot-password', ['email' => 'nobody@example.com']);

    // Same status and same body — the response must not be an oracle for
    // "does this address have an account?".
    expect($unknown->status())->toBe($known->status())
        ->and($unknown->json())->toBe($known->json());
});

it('throttles password reset requests', function () {
    Notification::fake();

    $user = User::factory()->create();

    // The per-email limit (5/hour) bites before the per-IP one (10/minute).
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();
    }

    $this->postJson('/api/forgot-password', ['email' => $user->email])->assertStatus(429);
});

it('throttles registration', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/register', [
            'name' => "User {$i}",
            'email' => "user{$i}@example.com",
            'password' => 'Password!234',
            'password_confirmation' => 'Password!234',
        ])->assertNoContent();
    }

    $this->postJson('/api/register', [
        'name' => 'One Too Many',
        'email' => 'toomany@example.com',
        'password' => 'Password!234',
        'password_confirmation' => 'Password!234',
    ])->assertStatus(429);
});
