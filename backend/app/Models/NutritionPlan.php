<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * generated_plan holds everything the calorie target column doesn't:
 * {"items": [...], "protein_g": int, "fat_g": int, "carbs_g": int} — the
 * daily macro targets alongside the day-by-day meal snapshot.
 *
 * @property int $id
 * @property int $user_id
 * @property string $goal
 * @property int $daily_calorie_target
 * @property array{items: list<array<string, mixed>>, protein_g: int, fat_g: int, carbs_g: int} $generated_plan
 */
class NutritionPlan extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'goal',
        'daily_calorie_target',
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
}
