<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

class ExecuteSetRequest extends ApiFormRequest
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
            'set_number' => 'required|integer|min:1',
            'reps_completed' => 'required|integer|min:1|max:999',
            'weight_used' => 'nullable|numeric|min:0|max:999.99',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'set_number.required' => 'El número de serie es requerido',
            'set_number.integer' => 'El número de serie debe ser un número entero',
            'set_number.min' => 'El número de serie debe ser mayor o igual a 1',
            'reps_completed.required' => 'Las repeticiones completadas son requeridas',
            'reps_completed.integer' => 'Las repeticiones completadas deben ser un número entero',
            'reps_completed.min' => 'Las repeticiones completadas deben ser mayor o igual a 1',
            'reps_completed.max' => 'Las repeticiones completadas no pueden superar 999',
            'weight_used.numeric' => 'El peso usado debe ser un número',
            'weight_used.min' => 'El peso usado debe ser mayor o igual a 0',
            'weight_used.max' => 'El peso usado no puede superar 999.99 kg',
        ];
    }
}
