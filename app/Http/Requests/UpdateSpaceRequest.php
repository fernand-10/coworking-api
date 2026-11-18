<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'space_type_id' => ['sometimes', 'exists:space_types,id'],
            'name' => ['sometimes', 'string', 'max:150'],
            'floor' => ['sometimes', 'integer', 'min:1'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'price_per_hour' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
