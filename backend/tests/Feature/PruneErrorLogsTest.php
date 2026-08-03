<?php

use App\Models\ErrorLog;

it('prunes entries older than the retention window and leaves newer ones', function () {
    config(['error_logs.retention_days' => 30]);

    $old = ErrorLog::factory()->create(['created_at' => now()->subDays(31)]);
    $recent = ErrorLog::factory()->create(['created_at' => now()->subDays(29)]);

    $this->artisan('error-logs:prune')->assertSuccessful();

    expect(ErrorLog::find($old->id))->toBeNull()
        ->and(ErrorLog::find($recent->id))->not->toBeNull();
});
