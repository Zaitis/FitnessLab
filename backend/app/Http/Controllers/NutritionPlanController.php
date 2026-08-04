<?php

namespace App\Http\Controllers;

use App\Application\Documents\Actions\ExportPlanToPdfAction;
use App\Application\Nutrition\Actions\GenerateNutritionPlanAction;
use App\Application\Nutrition\Actions\ListNutritionPlansAction;
use App\Domain\Nutrition\Enums\Goal;
use App\Http\Requests\StoreNutritionPlanRequest;
use App\Http\Resources\NutritionPlanResource;
use App\Models\NutritionPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final class NutritionPlanController extends Controller
{
    public function index(Request $request, ListNutritionPlansAction $action): AnonymousResourceCollection
    {
        return NutritionPlanResource::collection($action->execute($request->user()));
    }

    public function store(StoreNutritionPlanRequest $request, GenerateNutritionPlanAction $action): NutritionPlanResource
    {
        $plan = $action->execute($request->user(), Goal::from($request->string('goal')->value()));

        return new NutritionPlanResource($plan);
    }

    public function show(NutritionPlan $nutritionPlan): NutritionPlanResource
    {
        Gate::authorize('view', $nutritionPlan);

        return new NutritionPlanResource($nutritionPlan);
    }

    public function export(NutritionPlan $nutritionPlan, ExportPlanToPdfAction $action): Response
    {
        Gate::authorize('view', $nutritionPlan);

        $document = $action->execute($nutritionPlan);

        return new Response($document->contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$document->filename.'"',
        ]);
    }
}
