<?php

namespace App\Application\Workouts\Actions;

use App\Domain\Workouts\Criteria\WorkoutPlanCriteria;
use App\Domain\Workouts\Strategies\WorkoutPlanStrategy;
use App\Domain\Workouts\ValueObjects\WorkoutPlanItem;
use App\Models\User;
use App\Models\WorkoutPlan;
use Illuminate\Support\Str;
use RuntimeException;

final class GenerateWorkoutPlanAction
{
    /**
     * @param  iterable<WorkoutPlanStrategy>  $strategies
     */
    public function __construct(private readonly iterable $strategies) {}

    public function execute(User $user, WorkoutPlanCriteria $criteria): WorkoutPlan
    {
        $plan = $this->resolve($criteria)->generate($criteria);

        return WorkoutPlan::create([
            'user_id' => $user->id,
            'goal' => $criteria->goal->value,
            'experience_level' => $criteria->experienceLevel->value,
            'days_per_week' => $criteria->daysPerWeek,
            'generated_plan' => array_map($this->toSnapshotItem(...), $plan->items),
        ]);
    }

    private function resolve(WorkoutPlanCriteria $criteria): WorkoutPlanStrategy
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($criteria)) {
                return $strategy;
            }
        }

        throw new RuntimeException('No workout plan strategy supports the given criteria.');
    }

    /**
     * @return array<string, mixed>
     */
    private function toSnapshotItem(WorkoutPlanItem $item): array
    {
        return [
            'id' => (string) Str::uuid(),
            'day' => $item->day,
            'type' => $item->type->value,
            'name' => $item->name,
            'instructions' => $item->instructions,
            'sets' => $item->sets,
            'reps' => $item->reps,
            'duration_minutes' => $item->durationMinutes,
        ];
    }
}
