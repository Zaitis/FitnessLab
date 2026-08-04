<?php

use App\Models\BmiMeasurement;
use App\Models\User;
use Smalot\PdfParser\Parser;

/**
 * Word-wrapped lines (the disclaimer paragraph spans several visual lines)
 * come back from the parser with a newline at each wrap point — collapse
 * all whitespace so a wrapped sentence can still be matched as one string.
 */
function pdfTextOf(string $contents): string
{
    $text = (new Parser)->parseContent($contents)->getText();

    return preg_replace('/\s+/', ' ', $text);
}

it('rejects guests exporting either plan type with 401', function () {
    $this->getJson('/api/workout-plans/1/export')->assertStatus(401);
    $this->getJson('/api/nutrition-plans/1/export')->assertStatus(401);
});

it('exports a workout plan as a pdf containing the disclaimer', function () {
    seedMinimalCatalogue();
    $user = User::factory()->create();

    $planId = $this->actingAs($user)
        ->postJson('/api/workout-plans', validWorkoutPlanPayload())
        ->json('id');

    $response = $this->actingAs($user)->get("/api/workout-plans/{$planId}/export");

    $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
    expect($response->headers->get('Content-Disposition'))->toContain("workout-plan-{$planId}.pdf");

    $text = pdfTextOf($response->getContent());
    expect($text)->toContain(config('disclaimer.standard.en'))
        ->and($text)->toContain('Fat loss');
});

it('exports a nutrition plan as a pdf containing the disclaimer', function () {
    seedMinimalMealCatalogue();
    $user = User::factory()->create();
    BmiMeasurement::factory()->for($user)->create();

    $planId = $this->actingAs($user)
        ->postJson('/api/nutrition-plans', ['goal' => 'fat_loss'])
        ->json('id');

    $response = $this->actingAs($user)->get("/api/nutrition-plans/{$planId}/export");

    $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
    expect($response->headers->get('Content-Disposition'))->toContain("nutrition-plan-{$planId}.pdf");

    $text = pdfTextOf($response->getContent());
    expect($text)->toContain(config('disclaimer.standard.en'));
});

it('rejects a cross-user workout plan export with 403', function () {
    seedMinimalCatalogue();
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $planId = $this->actingAs($owner)
        ->postJson('/api/workout-plans', validWorkoutPlanPayload())
        ->json('id');

    $this->actingAs($intruder)->getJson("/api/workout-plans/{$planId}/export")->assertStatus(403);
});

it('rejects a cross-user nutrition plan export with 403', function () {
    seedMinimalMealCatalogue();
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    BmiMeasurement::factory()->for($owner)->create();

    $planId = $this->actingAs($owner)
        ->postJson('/api/nutrition-plans', ['goal' => 'fat_loss'])
        ->json('id');

    $this->actingAs($intruder)->getJson("/api/nutrition-plans/{$planId}/export")->assertStatus(403);
});
