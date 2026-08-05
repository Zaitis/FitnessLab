<?php

use App\Application\Documents\Actions\ExportPlanToPdfAction;
use App\Domain\Documents\Contracts\PdfExporterInterface;
use App\Domain\Documents\ValueObjects\PdfDocument;
use App\Domain\Documents\ValueObjects\PlanExportData;
use App\Models\NutritionPlan;
use App\Models\WorkoutPlan;
use Tests\TestCase;

// Unlike the pure-domain unit tests elsewhere in this suite, this Action
// legitimately touches the container (app()->getLocale(), config(), trans())
// -- it is Application layer, not Domain, and Pest.php's TestCase binding
// only covers tests/Feature. Opting in here, file-scoped, rather than
// widening that binding to all of tests/Unit and losing the container-free
// guarantee the Domain tests rely on.
uses(TestCase::class);

/**
 * Captures the PlanExportData handed to it instead of rendering anything —
 * this is the whole point of the interface (docs/DESIGN-PATTERNS.md §6):
 * Action tests substitute a fake exporter and assert on the data, no PDF
 * parsing involved.
 */
class FakePdfExporterForTest implements PdfExporterInterface
{
    public ?PlanExportData $received = null;

    public function export(PlanExportData $data): PdfDocument
    {
        $this->received = $data;

        return new PdfDocument(contents: 'fake-pdf-bytes', filename: $data->filename);
    }
}

function fakeWorkoutPlan(): WorkoutPlan
{
    $plan = new WorkoutPlan([
        'goal' => 'fat_loss',
        'experience_level' => 'beginner',
        'days_per_week' => 2,
        'generated_plan' => [
            [
                'id' => 'item-1',
                'day' => 1,
                'type' => 'strength',
                'name' => ['en' => 'Bench Press', 'pl' => 'Wyciskanie sztangi'],
                'instructions' => ['en' => 'Press.', 'pl' => 'Wciśnij.'],
                'sets' => 3,
                'reps' => 10,
                'duration_minutes' => null,
            ],
            [
                'id' => 'item-2',
                'day' => 1,
                'type' => 'cardio',
                'name' => ['en' => 'Walking', 'pl' => 'Spacer'],
                'instructions' => ['en' => 'Walk.', 'pl' => 'Idź.'],
                'sets' => null,
                'reps' => null,
                'duration_minutes' => 30,
            ],
        ],
    ]);
    $plan->id = 42;

    return $plan;
}

function fakeNutritionPlan(): NutritionPlan
{
    $plan = new NutritionPlan([
        'goal' => 'muscle_gain',
        'daily_calorie_target' => 2500,
        'generated_plan' => [
            'protein_g' => 180,
            'fat_g' => 80,
            'carbs_g' => 280,
            'items' => [
                [
                    'id' => 'meal-1',
                    'day' => 1,
                    'meal_time' => 'breakfast',
                    'calories' => 500,
                    'protein_g' => 30,
                    'fat_g' => 15,
                    'carbs_g' => 60,
                    'name' => ['en' => 'Oatmeal', 'pl' => 'Owsianka'],
                    'description' => ['en' => 'With banana.', 'pl' => 'Z bananem.'],
                ],
            ],
        ],
    ]);
    $plan->id = 7;

    return $plan;
}

it('passes correctly built export data for a workout plan', function () {
    $exporter = new FakePdfExporterForTest;
    $action = new ExportPlanToPdfAction($exporter);

    $document = $action->execute(fakeWorkoutPlan());

    expect($document->filename)->toBe('workout-plan-42.pdf')
        ->and($exporter->received->filename)->toBe('workout-plan-42.pdf')
        ->and($exporter->received->title)->toBe('Fat loss')
        ->and($exporter->received->summary)->toBe('Beginner · 2 days a week')
        ->and($exporter->received->disclaimer)->toBe(config('disclaimer.standard.en'))
        ->and($exporter->received->sections)->toHaveCount(1);

    $section = $exporter->received->sections[0];
    expect($section->heading)->toBe('Day 1')
        ->and($section->lines)->toBe([
            'Strength — Bench Press: 3 × 10',
            'Cardio — Walking: 30 min',
        ]);
});

it('passes correctly built export data for a nutrition plan', function () {
    $exporter = new FakePdfExporterForTest;
    $action = new ExportPlanToPdfAction($exporter);

    $action->execute(fakeNutritionPlan());

    expect($exporter->received->filename)->toBe('nutrition-plan-7.pdf')
        ->and($exporter->received->title)->toBe('Muscle gain')
        ->and($exporter->received->summary)->toBe('2500 kcal a day · 180g protein · 80g fat · 280g carbs')
        ->and($exporter->received->sections)->toHaveCount(1);

    $section = $exporter->received->sections[0];
    expect($section->heading)->toBe('Day 1')
        ->and($section->lines)->toBe(['Breakfast — Oatmeal (500 kcal)']);
});

it('builds the export in the currently active locale', function () {
    app()->setLocale('pl');

    $exporter = new FakePdfExporterForTest;
    $action = new ExportPlanToPdfAction($exporter);

    $action->execute(fakeWorkoutPlan());

    expect($exporter->received->locale)->toBe('pl')
        ->and($exporter->received->title)->toBe('Redukcja tkanki tłuszczowej')
        ->and($exporter->received->sections[0]->heading)->toBe('Dzień 1')
        ->and($exporter->received->disclaimer)->toBe(config('disclaimer.standard.pl'));

    app()->setLocale('en');
});
