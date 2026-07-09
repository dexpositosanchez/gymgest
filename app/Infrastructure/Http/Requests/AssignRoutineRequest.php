<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignRoutineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'routine_id' => 'required|uuid|exists:routines,id',
            'starts_at' => 'required|date',
            'is_current' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'routine_id.required' => 'El ID de la rutina es obligatorio',
            'routine_id.uuid' => 'El ID de la rutina debe ser un UUID válido',
            'routine_id.exists' => 'La rutina especificada no existe',
            'starts_at.required' => 'La fecha de inicio es obligatoria',
            'starts_at.date' => 'La fecha de inicio debe ser una fecha válida',
            'is_current.boolean' => 'El campo is_current debe ser verdadero o falso',
            'notes.string' => 'Las notas deben ser texto',
            'notes.max' => 'Las notas no pueden superar los 1000 caracteres',
        ];
    }
}
