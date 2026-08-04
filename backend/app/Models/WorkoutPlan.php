<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $goal
 * @property string $experience_level
 * @property int $days_per_week
 * @property list<array<string, mixed>> $generated_plan
 */
class WorkoutPlan extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'goal',
        'experience_level',
        'days_per_week',
        'generated_plan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'generated_plan' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * UUIDs of every item in this plan's snapshot — the set an adherence
     * entry's plan_item_id is allowed to reference.
     *
     * @return list<string>
     */
    public function itemIds(): array
    {
        return array_column($this->generated_plan, 'id');
    }

    /**
     * UUIDs of the items scheduled for one structural day of the plan.
     *
     * @return list<string>
     */
    public function itemIdsForDay(int $day): array
    {
        return array_column(
            array_filter($this->generated_plan, fn (array $item) => $item['day'] === $day),
            'id',
        );
    }
}
