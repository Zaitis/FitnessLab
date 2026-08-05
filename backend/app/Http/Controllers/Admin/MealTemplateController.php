<?php

namespace App\Http\Controllers\Admin;

use App\Application\Nutrition\Actions\CreateMealTemplateAction;
use App\Application\Nutrition\Actions\DeleteMealTemplateAction;
use App\Application\Nutrition\Actions\ListMealTemplatesAction;
use App\Application\Nutrition\Actions\UpdateMealTemplateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MealTemplateRequest;
use App\Models\MealTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class MealTemplateController extends Controller
{
    public function index(ListMealTemplatesAction $action): JsonResponse
    {
        return response()->json(
            $action->execute()->map($this->present(...))->values()
        );
    }

    public function store(MealTemplateRequest $request, CreateMealTemplateAction $action): JsonResponse
    {
        $mealTemplate = $action->execute($request->validated());

        return response()->json($this->present($mealTemplate), 201);
    }

    public function update(
        MealTemplateRequest $request,
        MealTemplate $mealTemplate,
        UpdateMealTemplateAction $action,
    ): JsonResponse {
        $mealTemplate = $action->execute($mealTemplate, $request->validated());

        return response()->json($this->present($mealTemplate));
    }

    public function destroy(MealTemplate $mealTemplate, DeleteMealTemplateAction $action): Response
    {
        $action->execute($mealTemplate);

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function present(MealTemplate $mealTemplate): array
    {
        return [
            'id' => $mealTemplate->id,
            'meal_time' => $mealTemplate->meal_time,
            'calories' => $mealTemplate->calories,
            'protein_g' => $mealTemplate->protein_g,
            'fat_g' => $mealTemplate->fat_g,
            'carbs_g' => $mealTemplate->carbs_g,
            // Every locale, not just the current request's — the admin form
            // edits all of them at once.
            'name' => $mealTemplate->getTranslations('name'),
            'description' => $mealTemplate->getTranslations('description'),
        ];
    }
}
