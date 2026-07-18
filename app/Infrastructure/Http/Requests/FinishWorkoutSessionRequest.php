<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

class FinishWorkoutSessionRequest extends ApiFormRequest
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
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'notes.string' => 'Las notas deben ser texto',
            'notes.max' => 'Las notas no pueden superar 500 caracteres',
        ];
    }
}
