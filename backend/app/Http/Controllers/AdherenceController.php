<?php

namespace App\Http\Controllers;

use App\Application\Adherence\Actions\GetMonthAdherenceAction;
use App\Application\Adherence\Actions\ToggleAdherenceAction;
use App\Domain\Adherence\Enums\PlanType;
use App\Http\Requests\ToggleAdherenceRequest;
use App\Models\AdherenceEntry;
use App\Models\NutritionPlan;
use App\Models\WorkoutPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

final class AdherenceController extends Controller
{
    public function index(Request $request, GetMonthAdherenceAction $action): JsonResponse
    {
        $entries = $action->execute($request->user(), $this->resolveMonth($request->query('month')));

        return response()->json($entries->map($this->present(...))->values());
    }

    public function store(ToggleAdherenceRequest $request, ToggleAdherenceAction $action): JsonResponse
    {
        return $this->toggle($request, $action, checked: true);
    }

    public function destroy(ToggleAdherenceRequest $request, ToggleAdherenceAction $action): JsonResponse
    {
        return $this->toggle($request, $action, checked: false);
    }

    private function toggle(ToggleAdherenceRequest $request, ToggleAdherenceAction $action, bool $checked): JsonResponse
    {
        $plan = $this->findPlan(
            PlanType::from($request->string('plan_type')->value()),
            $request->integer('plan_id'),
        );

        Gate::authorize('view', $plan);

        $action->execute(
            user: $request->user(),
            plan: $plan,
            entryDate: $request->date('entry_date')->toDateString(),
            planItemId: $request->string('plan_item_id')->value(),
            checked: $checked,
        );

        return response()->json(['checked' => $checked]);
    }

    private function findPlan(PlanType $planType, int $planId): WorkoutPlan|NutritionPlan
    {
        return match ($planType) {
            PlanType::Workout => WorkoutPlan::findOrFail($planId),
            PlanType::Nutrition => NutritionPlan::findOrFail($planId),
        };
    }

    /**
     * Anything that isn't a well-formed `YYYY-MM` falls back to the current
     * month rather than erroring — the parameter is a view hint, not input
     * the caller can corrupt anything with.
     */
    private function resolveMonth(mixed $month): Carbon
    {
        if (! is_string($month) || preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) !== 1) {
            return Carbon::now();
        }

        return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
    }

    /**
     * @return array<string, mixed>
     */
    private function present(AdherenceEntry $entry): array
    {
        return [
            'entry_date' => $entry->entry_date->toDateString(),
            'plan_type' => $entry->plan_type,
            'plan_id' => $entry->plan_id,
            'plan_item_id' => $entry->plan_item_id,
        ];
    }
}
