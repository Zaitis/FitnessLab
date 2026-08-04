<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $goal
 * @property int $daily_calorie_target
 * @property array{items: list<array<string, mixed>>, protein_g: int, fat_g: int, carbs_g: int} $generated_plan
 * @property Carbon $created_at
 */
class NutritionPlanResource extends JsonResource
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
            'daily_calorie_target' => $this->daily_calorie_target,
            'daily_protein_target_g' => $this->generated_plan['protein_g'],
            'daily_fat_target_g' => $this->generated_plan['fat_g'],
            'daily_carbs_target_g' => $this->generated_plan['carbs_g'],
            'items' => array_map(fn (array $item) => [
                'id' => $item['id'],
                'day' => $item['day'],
                'meal_time' => $item['meal_time'],
                'calories' => $item['calories'],
                'protein_g' => $item['protein_g'],
                'fat_g' => $item['fat_g'],
                'carbs_g' => $item['carbs_g'],
                'name' => $item['name'][$locale] ?? $item['name'][$fallback],
                'description' => $item['description'][$locale] ?? $item['description'][$fallback],
            ], $this->generated_plan['items']),
            'disclaimer' => config("disclaimer.standard.{$locale}", config("disclaimer.standard.{$fallback}")),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
