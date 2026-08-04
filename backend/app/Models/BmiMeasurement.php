<?php

namespace App\Models;

use Database\Factories\BmiMeasurementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property float $weight_kg
 * @property float $height_cm
 * @property int|null $age
 * @property string|null $sex
 * @property string|null $activity_level
 * @property float $bmi_value
 * @property string $category
 * @property Carbon $measured_on
 */
class BmiMeasurement extends Model
{
    /** @use HasFactory<BmiMeasurementFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'weight_kg',
        'height_cm',
        'age',
        'sex',
        'activity_level',
        'bmi_value',
        'category',
        'measured_on',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight_kg' => 'float',
            'height_cm' => 'float',
            'age' => 'integer',
            'bmi_value' => 'float',
            'measured_on' => 'date',
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
