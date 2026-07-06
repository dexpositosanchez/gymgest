<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'muscle_group_id' => ['required', 'exists:muscle_groups,id']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del ejercicio es requerido',
            'name.string' => 'El nombre debe ser un texto válido',
            'name.max' => 'El nombre no puede exceder los 255 caracteres',
            'description.required' => 'La descripción es requerida',
            'description.string' => 'La descripción debe ser un texto válido',
            'description.min' => 'La descripción debe tener al menos 10 caracteres',
            'muscle_group_id.required' => 'El grupo muscular es requerido',
            'muscle_group_id.exists' => 'El grupo muscular seleccionado no existe'
        ];
    }
}
