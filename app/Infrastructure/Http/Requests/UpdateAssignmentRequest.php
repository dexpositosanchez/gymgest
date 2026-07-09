<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'starts_at' => 'date',
            'is_current' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'starts_at.date' => 'La fecha de inicio debe ser una fecha válida',
            'is_current.boolean' => 'El campo is_current debe ser verdadero o falso',
            'notes.string' => 'Las notas deben ser texto',
            'notes.max' => 'Las notas no pueden superar los 1000 caracteres',
        ];
    }
}
