<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

class StartWorkoutSessionRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'routine_assignment_id' => 'required|uuid|exists:routine_assignments,id',
            'day_number' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'routine_assignment_id.required' => 'El ID de asignación de rutina es requerido',
            'routine_assignment_id.uuid' => 'El ID de asignación de rutina debe ser un UUID válido',
            'routine_assignment_id.exists' => 'La asignación de rutina no existe',
            'day_number.required' => 'El número de día es requerido',
            'day_number.integer' => 'El número de día debe ser un número entero',
            'day_number.min' => 'El número de día debe ser mayor o igual a 1',
            'notes.string' => 'Las notas deben ser texto',
            'notes.max' => 'Las notas no pueden superar 500 caracteres',
        ];
    }
}
