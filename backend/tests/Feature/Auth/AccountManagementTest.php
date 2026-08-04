<?php

use App\Models\User;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

// --- password ---

it('rejects guests updating the password with 401', function () {
    $this->patchJson('/api/user/password', [])->assertStatus(401);
});

it('lets an ordinary user change their password', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);

    $this->actingAs($user)->patchJson('/api/user/password', [
        'current_password' => 'old-password',
        'password' => 'new-password-1234',
        'password_confirmation' => 'new-password-1234',
    ])->assertNoContent();

    expect(Hash::check('new-password-1234', $user->fresh()->password))->toBeTrue();
});

it('rejects a password change with the wrong current password', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);

    $this->actingAs($user)->patchJson('/api/user/password', [
        'current_password' => 'not-the-password',
        'password' => 'new-password-1234',
        'password_confirmation' => 'new-password-1234',
    ])->assertStatus(422)->assertJsonValidationErrors('current_password');
});

it('rejects a password change for the demo account with 403', function () {
    $user = User::factory()->create(['is_demo' => true, 'password' => Hash::make('old-password')]);

    $this->actingAs($user)->patchJson('/api/user/password', [
        'current_password' => 'old-password',
        'password' => 'new-password-1234',
        'password_confirmation' => 'new-password-1234',
    ])->assertStatus(403);

    expect(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
});

// --- email ---

it('rejects guests updating the email with 401', function () {
    $this->patchJson('/api/user/email', [])->assertStatus(401);
});

it('lets an ordinary user change their email and resets verification', function () {
    Notification::fake();
    $user = User::factory()->create(['password' => Hash::make('the-password')]);

    $this->actingAs($user)->patchJson('/api/user/email', [
        'current_password' => 'the-password',
        'email' => 'new-address@example.com',
    ])->assertOk()->assertJsonPath('email', 'new-address@example.com');

    $fresh = $user->fresh();
    expect($fresh->email)->toBe('new-address@example.com')
        ->and($fresh->email_verified_at)->toBeNull();

    Notification::assertSentTo($fresh, QueuedVerifyEmail::class);
});

it('rejects an email already taken by another account', function () {
    $existing = User::factory()->create();
    $user = User::factory()->create(['password' => Hash::make('the-password')]);

    $this->actingAs($user)->patchJson('/api/user/email', [
        'current_password' => 'the-password',
        'email' => $existing->email,
    ])->assertStatus(422)->assertJsonValidationErrors('email');
});

it('rejects an email change for the demo account with 403', function () {
    $user = User::factory()->create(['is_demo' => true, 'password' => Hash::make('the-password')]);
    $originalEmail = $user->email;

    $this->actingAs($user)->patchJson('/api/user/email', [
        'current_password' => 'the-password',
        'email' => 'someone-else@example.com',
    ])->assertStatus(403);

    expect($user->fresh()->email)->toBe($originalEmail);
});

// --- account deletion ---

it('rejects guests deleting the account with 401', function () {
    $this->deleteJson('/api/user', [])->assertStatus(401);
});

it('lets an ordinary user delete their account', function () {
    $user = User::factory()->create(['password' => Hash::make('the-password')]);

    $this->actingAs($user)->deleteJson('/api/user', [
        'current_password' => 'the-password',
    ])->assertNoContent();

    expect(User::find($user->id))->toBeNull();
});

it('rejects account deletion with the wrong current password', function () {
    $user = User::factory()->create(['password' => Hash::make('the-password')]);

    $this->actingAs($user)->deleteJson('/api/user', [
        'current_password' => 'not-the-password',
    ])->assertStatus(422)->assertJsonValidationErrors('current_password');

    expect(User::find($user->id))->not->toBeNull();
});

it('rejects account deletion for the demo account with 403', function () {
    $user = User::factory()->create(['is_demo' => true, 'password' => Hash::make('the-password')]);

    $this->actingAs($user)->deleteJson('/api/user', [
        'current_password' => 'the-password',
    ])->assertStatus(403);

    expect(User::find($user->id))->not->toBeNull();
});
