<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

test('email can be verified without an active session', function () {
    // The link is opened from an email client, essentially never in the
    // browser session the user registered in — this is the case that
    // matters, not an edge case.
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(config('app.frontend_url').'/dashboard?verified=1');
});

test('an already-verified email redirects without re-dispatching the event', function () {
    $user = User::factory()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->get($verificationUrl);

    Event::assertNotDispatched(Verified::class);
    $response->assertRedirect(config('app.frontend_url').'/dashboard?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $response = $this->get($verificationUrl);

    $response->assertNotFound();
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('email is not verified for a nonexistent user id', function () {
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => 999999, 'hash' => sha1('whatever')]
    );

    $this->get($verificationUrl)->assertNotFound();
});
