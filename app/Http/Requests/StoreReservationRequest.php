<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'space_id' => ['required', 'exists:spaces,id'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'space_id.required' => 'Debe seleccionar un espacio de coworking.',
            'space_id.exists' => 'El espacio seleccionado no existe.',
            'start_time.required' => 'La fecha y hora de inicio son obligatorias.',
            'start_time.after' => 'La reserva debe realizarse para una fecha y hora futura.',
            'end_time.required' => 'La fecha y hora de finalización son obligatorias.',
            'end_time.after' => 'La hora de finalización debe ser posterior a la de inicio.',
        ];
    }
}
