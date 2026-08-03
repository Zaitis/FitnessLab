<?php

use App\Models\ErrorLog;
use App\Models\User;

it('rejects guests with 401', function () {
    $this->getJson('/api/admin/logs')->assertStatus(401);
});

it('rejects an authenticated non-admin with 403', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->getJson('/api/admin/logs')->assertStatus(403);
});

it('lets an admin list error logs', function () {
    $admin = User::factory()->admin()->create();
    ErrorLog::factory()->count(3)->create();

    $this->actingAs($admin)->getJson('/api/admin/logs')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('filters by level', function () {
    $admin = User::factory()->admin()->create();
    ErrorLog::factory()->create(['level' => 'error']);
    ErrorLog::factory()->create(['level' => 'warning']);

    $response = $this->actingAs($admin)->getJson('/api/admin/logs?level=error')->assertOk();

    $response->assertJsonCount(1, 'data');
    expect($response->json('data.0.level'))->toBe('error');
});

it('rejects an invalid level filter with 422', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->getJson('/api/admin/logs?level=not-a-real-level')
        ->assertStatus(422);
});
