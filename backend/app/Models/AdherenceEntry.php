<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $entry_date
 * @property string $plan_type
 * @property int $plan_id
 * @property string $plan_item_id
 * @property Carbon $completed_at
 */
class AdherenceEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'entry_date',
        'plan_type',
        'plan_id',
        'plan_item_id',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'completed_at' => 'datetime',
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
