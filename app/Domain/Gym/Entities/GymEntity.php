<?php

declare(strict_types=1);

namespace App\Domain\Gym\Entities;

use App\Domain\Gym\ValueObjects\GymAddress;
use App\Domain\Gym\ValueObjects\GymLocality;
use App\Domain\Gym\ValueObjects\GymProvince;
use App\Domain\Gym\ValueObjects\GymCountry;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\Gym\ValueObjects\GymName;
use App\Domain\User\ValueObjects\UserId;

final class GymEntity
{
    private $id;
    private $trainerId;
    private $name;
    private $address;
    private $locality;
    private $province;
    private $country;
    private $isActive;

    public function __construct(
        GymId $id,
        UserId $trainerId,
        GymName $name,
        GymAddress $address,
        GymLocality $locality,
        GymProvince $province,
        GymCountry $country,
        bool $isActive = true
    ) {
        $this->id = $id;
        $this->trainerId = $trainerId;
        $this->name = $name;
        $this->address = $address;
        $this->locality = $locality;
        $this->province = $province;
        $this->country = $country;
        $this->isActive = $isActive;
    }

    public function getId(): GymId
    {
        return $this->id;
    }

    public function getTrainerId(): UserId
    {
        return $this->trainerId;
    }

    public function getName(): GymName
    {
        return $this->name;
    }

    public function getAddress(): GymAddress
    {
        return $this->address;
    }

    public function getLocality(): GymLocality
    {
        return $this->locality;
    }

    public function getProvince(): GymProvince
    {
        return $this->province;
    }

    public function getCountry(): GymCountry
    {
        return $this->country;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isAssigned(): bool
    {
        // Placeholder: will return true when gym_students table is implemented
        return false;
    }

    public function belongsToTrainer(UserId $trainerId): bool
    {
        return $this->trainerId->equals($trainerId);
    }

    public function updateName(GymName $name): void
    {
        $this->name = $name;
    }

    public function updateAddress(GymAddress $address): void
    {
        $this->address = $address;
    }

    public function updateLocality(GymLocality $locality): void
    {
        $this->locality = $locality;
    }

    public function updateProvince(GymProvince $province): void
    {
        $this->province = $province;
    }

    public function updateCountry(GymCountry $country): void
    {
        $this->country = $country;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }
}
