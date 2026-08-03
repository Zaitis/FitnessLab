<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculateBmiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'weight_kg' => ['required', 'numeric', 'min:1', 'max:500'],
            'height_cm' => ['required', 'numeric', 'min:30', 'max:250'],
        ];
    }
}
