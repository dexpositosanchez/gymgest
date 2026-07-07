<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoutineRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'days' => 'required|array|min:1',
            'days.*.day_number' => 'required|integer|min:1|max:7',
            'days.*.name' => 'required|string|max:255',
            'days.*.exercises' => 'required|array|min:1',
            'days.*.exercises.*.exercise_id' => 'required|uuid|exists:exercises,id',
            'days.*.exercises.*.order_index' => 'required|integer|min:0',
            'days.*.exercises.*.sets' => 'required|array|min:1',
            'days.*.exercises.*.sets.*.set_number' => 'required|integer|min:1',
            'days.*.exercises.*.sets.*.reps' => 'required|integer|min:1',
            'days.*.exercises.*.sets.*.notes' => 'nullable|string',
            'days.*.exercises.*.notes' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre de la rutina es obligatorio',
            'difficulty.required' => 'La dificultad es obligatoria',
            'difficulty.in' => 'La dificultad debe ser: beginner, intermediate o advanced',
            'days.required' => 'La rutina debe tener al menos un día',
            'days.min' => 'La rutina debe tener al menos un día',
            'days.*.day_number.min' => 'El número de día debe estar entre 1 y 7',
            'days.*.day_number.max' => 'El número de día debe estar entre 1 y 7',
            'days.*.exercises.required' => 'Cada día debe tener al menos un ejercicio',
            'days.*.exercises.min' => 'Cada día debe tener al menos un ejercicio',
            'days.*.exercises.*.sets.required' => 'Cada ejercicio debe tener al menos una serie',
            'days.*.exercises.*.sets.min' => 'Cada ejercicio debe tener al menos una serie',
            'days.*.exercises.*.sets.*.set_number.min' => 'El número de serie debe ser mayor a 0',
            'days.*.exercises.*.sets.*.reps.min' => 'El número de repeticiones debe ser mayor a 0',
        ];
    }
}
