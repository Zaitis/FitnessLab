<?php

namespace App\Http\Controllers;

use App\Application\Measurements\Actions\CalculateBmiAction;
use App\Domain\Measurements\ValueObjects\Height;
use App\Domain\Measurements\ValueObjects\Weight;
use App\Http\Requests\CalculateBmiRequest;
use Illuminate\Http\JsonResponse;

final class BmiController extends Controller
{
    public function calculate(CalculateBmiRequest $request, CalculateBmiAction $action): JsonResponse
    {
        $bmi = $action->execute(
            new Weight($request->float('weight_kg')),
            new Height($request->float('height_cm')),
        );

        return response()->json([
            'value' => $bmi->value,
            'category' => $bmi->category->value,
        ]);
    }
}
