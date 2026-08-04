<?php

use App\Models\ErrorLog;
use Illuminate\Support\Facades\Log;

it('writes error-level logs to the error_logs table', function () {
    Log::channel('database')->error('Something broke');

    expect(ErrorLog::where('message', 'Something broke')->exists())->toBeTrue();
});

it('redacts sensitive context keys before writing', function () {
    Log::channel('database')->error('Login failed', [
        'password' => 'super-secret',
        'token' => 'abc123',
        'email' => 'user@example.com',
    ]);

    $entry = ErrorLog::where('message', 'Login failed')->firstOrFail();

    expect($entry->context['password'])->toBe('[redacted]')
        ->and($entry->context['token'])->toBe('[redacted]')
        ->and($entry->context['email'])->toBe('user@example.com');
});

it('redacts compound keys that merely contain a sensitive fragment', function () {
    // `password_confirmation` is this application's own registration payload
    // key. An exact-match redaction list scrubbed `password` and wrote the
    // identical secret through beside it.
    Log::channel('database')->error('Registration failed', [
        'password' => 'super-secret',
        'password_confirmation' => 'super-secret',
        'access_token' => 'abc123',
        'X-XSRF-TOKEN' => 'xyz789',
        'name' => 'Ada Lovelace',
    ]);

    $entry = ErrorLog::where('message', 'Registration failed')->firstOrFail();

    expect($entry->context['password'])->toBe('[redacted]')
        ->and($entry->context['password_confirmation'])->toBe('[redacted]')
        ->and($entry->context['access_token'])->toBe('[redacted]')
        ->and($entry->context['X-XSRF-TOKEN'])->toBe('[redacted]')
        ->and($entry->context['name'])->toBe('Ada Lovelace');
});

it('redacts sensitive keys nested inside the context', function () {
    Log::channel('database')->error('Request failed', [
        'headers' => ['Authorization' => 'Bearer xyz', 'Accept' => 'application/json'],
    ]);

    $entry = ErrorLog::where('message', 'Request failed')->firstOrFail();

    expect($entry->context['headers']['Authorization'])->toBe('[redacted]')
        ->and($entry->context['headers']['Accept'])->toBe('application/json');
});
