<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'space_type_id' => ['required', 'exists:space_types,id'],
            'name' => ['required', 'string', 'max:150'],
            'floor' => ['required', 'integer', 'min:1'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'space_type_id.required' => 'El tipo de espacio es obligatorio.',
            'space_type_id.exists' => 'El tipo de espacio indicado no es válido.',
            'name.required' => 'El nombre del espacio es obligatorio.',
            'capacity.min' => 'La capacidad mínima debe ser al menos 1 persona.',
            'price_per_hour.min' => 'El precio por hora no puede ser negativo.',
        ];
    }
}
