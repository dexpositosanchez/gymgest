<?php

declare(strict_types=1);

namespace App\Application\Gym\DTOs;

final class UpdateGymDTO
{
    private $gymId;
    private $trainerId;
    private $name;
    private $address;
    private $locality;
    private $province;
    private $country;

    public function __construct(
        string $gymId,
        string $trainerId,
        string $name,
        string $address,
        string $locality,
        string $province,
        string $country
    ) {
        $this->gymId = $gymId;
        $this->trainerId = $trainerId;
        $this->name = $name;
        $this->address = $address;
        $this->locality = $locality;
        $this->province = $province;
        $this->country = $country;
    }

    public function getGymId(): string
    {
        return $this->gymId;
    }

    public function getTrainerId(): string
    {
        return $this->trainerId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getLocality(): string
    {
        return $this->locality;
    }

    public function getProvince(): string
    {
        return $this->province;
    }

    public function getCountry(): string
    {
        return $this->country;
    }
}
