<?php

namespace App\Http\Requests;

use App\Domain\Nutrition\Enums\Goal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNutritionPlanRequest extends FormRequest
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
        return [
            'goal' => ['required', Rule::enum(Goal::class)],
        ];
    }
}
