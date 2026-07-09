<?php

declare(strict_types=1);

namespace App\Application\Gym\DTOs;

final class GymResponseDTO
{
    private $id;
    private $trainerId;
    private $name;
    private $address;
    private $locality;
    private $province;
    private $country;
    private $isActive;
    private $isAssigned;
    private $activeStudentsCount;

    public function __construct(
        string $id,
        string $trainerId,
        string $name,
        string $address,
        string $locality,
        string $province,
        string $country,
        bool $isActive,
        bool $isAssigned,
        int $activeStudentsCount
    ) {
        $this->id = $id;
        $this->trainerId = $trainerId;
        $this->name = $name;
        $this->address = $address;
        $this->locality = $locality;
        $this->province = $province;
        $this->country = $country;
        $this->isActive = $isActive;
        $this->isAssigned = $isAssigned;
        $this->activeStudentsCount = $activeStudentsCount;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'trainer_id' => $this->trainerId,
            'name' => $this->name,
            'address' => $this->address,
            'locality' => $this->locality,
            'province' => $this->province,
            'country' => $this->country,
            'is_active' => $this->isActive,
            'is_assigned' => $this->isAssigned,
            'active_students_count' => $this->activeStudentsCount,
        ];
    }
}
