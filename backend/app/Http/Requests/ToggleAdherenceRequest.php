<?php

namespace App\Http\Requests;

use App\Domain\Adherence\Enums\PlanType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleAdherenceRequest extends FormRequest
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
            'entry_date' => ['required', 'date'],
            'plan_type' => ['required', Rule::enum(PlanType::class)],
            'plan_id' => ['required', 'integer', 'min:1'],
            'plan_item_id' => ['required', 'uuid'],
        ];
    }
}
