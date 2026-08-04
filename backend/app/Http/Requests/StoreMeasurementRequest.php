<?php

namespace App\Http\Requests;

use App\Domain\Measurements\Enums\ActivityLevel;
use App\Domain\Measurements\Enums\Sex;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMeasurementRequest extends FormRequest
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
            'weight_kg' => ['required', 'numeric', 'min:1', 'max:500'],
            'height_cm' => ['required', 'numeric', 'min:30', 'max:250'],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'sex' => ['required', Rule::enum(Sex::class)],
            'activity_level' => ['required', Rule::enum(ActivityLevel::class)],
        ];
    }
}
