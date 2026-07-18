<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use App\Domain\User\ValueObjects\UserType;
use App\Domain\User\ValueObjects\Gender;

use Illuminate\Validation\Rule;

class RegisterUserRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ],
            'user_type' => ['required', Rule::in(UserType::getValidTypes())],
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_date' => [
                'required',
                'date',
                'before:' . now()->subYears(16)->format('Y-m-d')
            ],
            'gender' => ['required', Rule::in(Gender::getValidGenders())]
        ];

        if ($this->input('user_type') === UserType::STUDENT) {
            $rules['gym_goals'] = ['required', 'string', 'max:1000'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El email es requerido',
            'email.email' => 'El email debe ser una dirección válida',
            'email.unique' => 'Este email ya está registrado',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número',
            'user_type.required' => 'El tipo de usuario es requerido',
            'user_type.in' => 'El tipo de usuario debe ser trainer o student',
            'name.required' => 'El nombre es requerido',
            'last_name.required' => 'El apellido es requerido',
            'birth_date.required' => 'La fecha de nacimiento es requerida',
            'birth_date.date' => 'La fecha de nacimiento debe ser una fecha válida',
            'birth_date.before' => 'Debes tener al menos 16 años para registrarte',
            'gender.required' => 'El género es requerido',
            'gender.in' => 'El género debe ser male, female u other',
            'gym_goals.required' => 'Los alumnos deben proporcionar sus objetivos en el gimnasio'
        ];
    }
}