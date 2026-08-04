<?php

namespace App\Application\Adherence\Actions;

use App\Domain\Adherence\Enums\PlanType;
use App\Models\AdherenceEntry;
use App\Models\NutritionPlan;
use App\Models\User;
use App\Models\WorkoutPlan;
use Illuminate\Validation\ValidationException;

final class ToggleAdherenceAction
{
    public function execute(
        User $user,
        WorkoutPlan|NutritionPlan $plan,
        string $entryDate,
        string $planItemId,
        bool $checked,
    ): void {
        if (! in_array($planItemId, $plan->itemIds(), true)) {
            throw ValidationException::withMessages([
                'plan_item_id' => 'That item does not belong to the given plan.',
            ]);
        }

        $identity = [
            'user_id' => $user->id,
            'entry_date' => $entryDate,
            'plan_item_id' => $planItemId,
        ];

        if (! $checked) {
            AdherenceEntry::query()->where($identity)->delete();

            return;
        }

        // The unique index on (user_id, entry_date, plan_item_id) is what
        // actually prevents duplicates; updateOrCreate keeps a double-submit
        // from surfacing as a 500 on the constraint violation.
        AdherenceEntry::updateOrCreate($identity, [
            'plan_type' => $plan instanceof WorkoutPlan ? PlanType::Workout->value : PlanType::Nutrition->value,
            'plan_id' => $plan->id,
            'completed_at' => now(),
        ]);
    }
}
