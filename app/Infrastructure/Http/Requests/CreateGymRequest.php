<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;



class CreateGymRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'locality' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'country' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del gimnasio es obligatorio',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'address.required' => 'La dirección es obligatoria',
            'address.max' => 'La dirección no puede exceder 255 caracteres',
            'locality.required' => 'La localidad es obligatoria',
            'locality.max' => 'La localidad no puede exceder 100 caracteres',
            'province.required' => 'La provincia es obligatoria',
            'province.max' => 'La provincia no puede exceder 100 caracteres',
            'country.required' => 'El país es obligatorio',
            'country.max' => 'El país no puede exceder 100 caracteres',
        ];
    }
}
