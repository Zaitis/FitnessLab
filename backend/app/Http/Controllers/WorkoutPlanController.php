<?php

namespace App\Http\Controllers;

use App\Application\Workouts\Actions\GenerateWorkoutPlanAction;
use App\Application\Workouts\Actions\ListWorkoutPlansAction;
use App\Domain\Workouts\Criteria\WorkoutPlanCriteria;
use App\Domain\Workouts\Enums\ExerciseLocation;
use App\Domain\Workouts\Enums\ExperienceLevel;
use App\Domain\Workouts\Enums\Goal;
use App\Http\Requests\StoreWorkoutPlanRequest;
use App\Http\Resources\WorkoutPlanResource;
use App\Models\WorkoutPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class WorkoutPlanController extends Controller
{
    public function index(Request $request, ListWorkoutPlansAction $action): AnonymousResourceCollection
    {
        return WorkoutPlanResource::collection($action->execute($request->user()));
    }

    public function store(StoreWorkoutPlanRequest $request, GenerateWorkoutPlanAction $action): WorkoutPlanResource
    {
        $criteria = new WorkoutPlanCriteria(
            goal: Goal::from($request->string('goal')->value()),
            experienceLevel: ExperienceLevel::from($request->string('experience_level')->value()),
            daysPerWeek: $request->integer('days_per_week'),
            location: ExerciseLocation::from($request->string('location')->value()),
        );

        return new WorkoutPlanResource($action->execute($request->user(), $criteria));
    }

    public function show(Request $request, WorkoutPlan $workoutPlan): WorkoutPlanResource|JsonResponse
    {
        if ($workoutPlan->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return new WorkoutPlanResource($workoutPlan);
    }
}
