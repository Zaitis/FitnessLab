<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $goal
 * @property string $experience_level
 * @property int $days_per_week
 * @property list<array<string, mixed>> $generated_plan
 * @property Carbon $created_at
 */
class WorkoutPlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $fallback = config('supported_locales.default');

        return [
            'id' => $this->id,
            'goal' => $this->goal,
            'experience_level' => $this->experience_level,
            'days_per_week' => $this->days_per_week,
            'items' => array_map(fn (array $item) => [
                'id' => $item['id'],
                'day' => $item['day'],
                'type' => $item['type'],
                'name' => $item['name'][$locale] ?? $item['name'][$fallback],
                'instructions' => $item['instructions'][$locale] ?? $item['instructions'][$fallback],
                'sets' => $item['sets'],
                'reps' => $item['reps'],
                'duration_minutes' => $item['duration_minutes'],
            ], $this->generated_plan),
            'disclaimer' => config("disclaimer.standard.{$locale}", config("disclaimer.standard.{$fallback}")),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
