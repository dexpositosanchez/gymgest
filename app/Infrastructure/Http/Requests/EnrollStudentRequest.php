<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gym_id' => 'nullable|uuid|exists:gyms,id',
            'email' => 'required|email',
            'quota_expires_at' => 'required|date|date_format:Y-m-d|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'gym_id.uuid' => 'El ID del gimnasio debe ser un UUID válido',
            'gym_id.exists' => 'El gimnasio no existe',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe ser válido',
            'quota_expires_at.required' => 'La fecha de caducidad de la cuota es obligatoria',
            'quota_expires_at.date' => 'La fecha de caducidad debe ser una fecha válida',
            'quota_expires_at.date_format' => 'La fecha debe estar en formato Y-m-d',
            'quota_expires_at.after' => 'La fecha de caducidad debe ser posterior a hoy',
        ];
    }
}
