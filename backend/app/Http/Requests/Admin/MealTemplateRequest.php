<?php

namespace App\Http\Requests\Admin;

use App\Domain\Nutrition\Enums\MealTime;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared by store and update, mirroring ExerciseRequest — an admin edit
 * form always submits the whole record.
 */
class MealTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = [
            'meal_time' => ['required', Rule::enum(MealTime::class)],
            'calories' => ['required', 'integer', 'min:0', 'max:5000'],
            'protein_g' => ['required', 'integer', 'min:0', 'max:500'],
            'fat_g' => ['required', 'integer', 'min:0', 'max:500'],
            'carbs_g' => ['required', 'integer', 'min:0', 'max:500'],
            'name' => ['required', 'array'],
            'description' => ['required', 'array'],
        ];

        foreach (config('supported_locales.supported') as $locale) {
            $rules["name.{$locale}"] = ['required', 'string', 'max:255'];
            $rules["description.{$locale}"] = ['required', 'string', 'max:2000'];
        }

        return $rules;
    }
}
