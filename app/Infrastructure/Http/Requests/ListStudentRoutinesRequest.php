<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListStudentRoutinesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:50',
            'gym_id' => 'sometimes|string|uuid',
            'trainer_id' => 'sometimes|string|uuid',
            'difficulty' => 'sometimes|string|in:beginner,intermediate,advanced',
            'from' => 'sometimes|date',
            'to' => 'sometimes|date|after_or_equal:from',
        ];
    }

    public function getFilters(): array
    {
        $filters = [];

        if ($this->has('gym_id')) {
            $filters['gym_id'] = $this->input('gym_id');
        }

        if ($this->has('trainer_id')) {
            $filters['trainer_id'] = $this->input('trainer_id');
        }

        if ($this->has('difficulty')) {
            $filters['difficulty'] = $this->input('difficulty');
        }

        if ($this->has('from')) {
            $filters['from'] = $this->input('from');
        }

        if ($this->has('to')) {
            $filters['to'] = $this->input('to');
        }

        return $filters;
    }

    public function getPage(): int
    {
        return (int) $this->input('page', 1);
    }

    public function getPerPage(): int
    {
        return (int) $this->input('per_page', 10);
    }
}
