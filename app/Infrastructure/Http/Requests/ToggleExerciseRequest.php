<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ToggleExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean']
        ];
    }

    public function messages(): array
    {
        return [
            'is_active.required' => 'El estado de activación es requerido',
            'is_active.boolean' => 'El estado de activación debe ser verdadero o falso'
        ];
    }
}
