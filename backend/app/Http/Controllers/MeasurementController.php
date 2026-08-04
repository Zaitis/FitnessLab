<?php

namespace App\Http\Controllers;

use App\Application\Measurements\Actions\ListMeasurementsAction;
use App\Application\Measurements\Actions\RecordMeasurementAction;
use App\Domain\Measurements\Enums\ActivityLevel;
use App\Domain\Measurements\Enums\Sex;
use App\Domain\Measurements\ValueObjects\Height;
use App\Domain\Measurements\ValueObjects\Weight;
use App\Http\Requests\StoreMeasurementRequest;
use App\Models\BmiMeasurement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MeasurementController extends Controller
{
    public function index(Request $request, ListMeasurementsAction $action): JsonResponse
    {
        $measurements = $action->execute($request->user())
            ->through(fn (BmiMeasurement $measurement) => $this->present($measurement));

        return response()->json($measurements);
    }

    public function store(StoreMeasurementRequest $request, RecordMeasurementAction $action): JsonResponse
    {
        $measurement = $action->execute(
            $request->user(),
            new Weight($request->float('weight_kg')),
            new Height($request->float('height_cm')),
            $request->integer('age'),
            Sex::from($request->string('sex')->value()),
            ActivityLevel::from($request->string('activity_level')->value()),
        );

        return response()->json($this->present($measurement), 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(BmiMeasurement $measurement): array
    {
        return [
            'id' => $measurement->id,
            'weight_kg' => $measurement->weight_kg,
            'height_cm' => $measurement->height_cm,
            'age' => $measurement->age,
            'sex' => $measurement->sex,
            'activity_level' => $measurement->activity_level,
            'value' => $measurement->bmi_value,
            'category' => $measurement->category,
            'measured_on' => $measurement->measured_on->toDateString(),
        ];
    }
}
