<?php

namespace App\Http\Controllers\Admin;

use App\Application\Workouts\Actions\CreateExerciseAction;
use App\Application\Workouts\Actions\DeleteExerciseAction;
use App\Application\Workouts\Actions\ListExercisesAction;
use App\Application\Workouts\Actions\UpdateExerciseAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExerciseRequest;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class ExerciseController extends Controller
{
    public function index(ListExercisesAction $action): JsonResponse
    {
        return response()->json(
            $action->execute()->map($this->present(...))->values()
        );
    }

    public function store(ExerciseRequest $request, CreateExerciseAction $action): JsonResponse
    {
        $exercise = $action->execute($request->validated());

        return response()->json($this->present($exercise), 201);
    }

    public function update(ExerciseRequest $request, Exercise $exercise, UpdateExerciseAction $action): JsonResponse
    {
        $exercise = $action->execute($exercise, $request->validated());

        return response()->json($this->present($exercise));
    }

    public function destroy(Exercise $exercise, DeleteExerciseAction $action): Response
    {
        $action->execute($exercise);

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Exercise $exercise): array
    {
        return [
            'id' => $exercise->id,
            'type' => $exercise->type,
            'location' => $exercise->location,
            'difficulty' => $exercise->difficulty,
            'muscle_group' => $exercise->muscle_group,
            'sets' => $exercise->sets,
            'reps' => $exercise->reps,
            'duration_minutes' => $exercise->duration_minutes,
            // Every locale, not just the current request's — the admin form
            // edits all of them at once.
            'name' => $exercise->getTranslations('name'),
            'instructions' => $exercise->getTranslations('instructions'),
        ];
    }
}
