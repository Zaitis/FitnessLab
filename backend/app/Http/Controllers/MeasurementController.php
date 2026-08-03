<?php

namespace App\Http\Controllers;

use App\Application\Measurements\Actions\RecordMeasurementAction;
use App\Domain\Measurements\ValueObjects\Height;
use App\Domain\Measurements\ValueObjects\Weight;
use App\Http\Requests\StoreMeasurementRequest;
use Illuminate\Http\JsonResponse;

final class MeasurementController extends Controller
{
    public function store(StoreMeasurementRequest $request, RecordMeasurementAction $action): JsonResponse
    {
        $measurement = $action->execute(
            $request->user(),
            new Weight($request->float('weight_kg')),
            new Height($request->float('height_cm')),
        );

        return response()->json([
            'id' => $measurement->id,
            'weight_kg' => $measurement->weight_kg,
            'height_cm' => $measurement->height_cm,
            'value' => $measurement->bmi_value,
            'category' => $measurement->category,
            'measured_on' => $measurement->measured_on->toDateString(),
        ], 201);
    }
}
