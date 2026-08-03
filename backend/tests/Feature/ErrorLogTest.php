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

it('redacts sensitive keys nested inside the context', function () {
    Log::channel('database')->error('Request failed', [
        'headers' => ['Authorization' => 'Bearer xyz', 'Accept' => 'application/json'],
    ]);

    $entry = ErrorLog::where('message', 'Request failed')->firstOrFail();

    expect($entry->context['headers']['Authorization'])->toBe('[redacted]')
        ->and($entry->context['headers']['Accept'])->toBe('application/json');
});
