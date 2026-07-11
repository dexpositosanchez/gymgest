<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Gym\Entities;

use App\Domain\Gym\Entities\GymEntity;
use App\Domain\Gym\ValueObjects\GymAddress;
use App\Domain\Gym\ValueObjects\GymLocality;
use App\Domain\Gym\ValueObjects\GymProvince;
use App\Domain\Gym\ValueObjects\GymCountry;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\Gym\ValueObjects\GymName;
use App\Domain\User\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

class GymEntityTest extends TestCase
{
    private function createGym(bool $isActive = true, bool $isPersonalTraining = false): GymEntity
    {
        return new GymEntity(
            new GymId('123e4567-e89b-12d3-a456-426614174000'),
            new UserId('223e4567-e89b-12d3-a456-426614174000'),
            new GymName('FitZone Madrid Centro'),
            new GymAddress('Calle Gran Vía, 123'),
            new GymLocality('Madrid'),
            new GymProvince('Comunidad de Madrid'),
            new GymCountry('España'),
            $isActive,
            $isPersonalTraining
        );
    }

    public function test_can_create_gym(): void
    {
        $gym = $this->createGym();

        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $gym->getId()->getValue());
        $this->assertEquals('223e4567-e89b-12d3-a456-426614174000', $gym->getTrainerId()->getValue());
        $this->assertEquals('FitZone Madrid Centro', $gym->getName()->getValue());
        $this->assertEquals('Calle Gran Vía, 123', $gym->getAddress()->getValue());
        $this->assertEquals('Madrid', $gym->getLocality()->getValue());
        $this->assertEquals('Comunidad de Madrid', $gym->getProvince()->getValue());
        $this->assertEquals('España', $gym->getCountry()->getValue());
        $this->assertTrue($gym->isActive());
    }

    public function test_gym_is_not_assigned_by_default(): void
    {
        $gym = $this->createGym();

        $this->assertFalse($gym->isAssigned());
    }

    public function test_gym_belongs_to_trainer(): void
    {
        $trainerId = new UserId('223e4567-e89b-12d3-a456-426614174000');
        $gym = $this->createGym();

        $this->assertTrue($gym->belongsToTrainer($trainerId));
    }

    public function test_gym_does_not_belong_to_different_trainer(): void
    {
        $differentTrainerId = new UserId('323e4567-e89b-12d3-a456-426614174000');
        $gym = $this->createGym();

        $this->assertFalse($gym->belongsToTrainer($differentTrainerId));
    }

    public function test_can_update_gym_name(): void
    {
        $gym = $this->createGym();
        $newName = new GymName('FitZone Madrid Norte');

        $gym->updateName($newName);

        $this->assertEquals('FitZone Madrid Norte', $gym->getName()->getValue());
    }

    public function test_can_update_gym_address(): void
    {
        $gym = $this->createGym();
        $newAddress = new GymAddress('Calle Serrano, 456');

        $gym->updateAddress($newAddress);

        $this->assertEquals('Calle Serrano, 456', $gym->getAddress()->getValue());
    }

    public function test_can_update_gym_locality(): void
    {
        $gym = $this->createGym();
        $newLocality = new GymLocality('Barcelona');

        $gym->updateLocality($newLocality);

        $this->assertEquals('Barcelona', $gym->getLocality()->getValue());
    }

    public function test_can_update_gym_province(): void
    {
        $gym = $this->createGym();
        $newProvince = new GymProvince('Cataluña');

        $gym->updateProvince($newProvince);

        $this->assertEquals('Cataluña', $gym->getProvince()->getValue());
    }

    public function test_can_update_gym_country(): void
    {
        $gym = $this->createGym();
        $newCountry = new GymCountry('Portugal');

        $gym->updateCountry($newCountry);

        $this->assertEquals('Portugal', $gym->getCountry()->getValue());
    }

    public function test_can_activate_gym(): void
    {
        $gym = $this->createGym(false);

        $gym->activate();

        $this->assertTrue($gym->isActive());
    }

    public function test_can_deactivate_gym(): void
    {
        $gym = $this->createGym(true);

        $gym->deactivate();

        $this->assertFalse($gym->isActive());
    }

    public function test_gym_is_not_personal_training_by_default(): void
    {
        $gym = $this->createGym();

        $this->assertFalse($gym->isPersonalTraining());
        $this->assertFalse($gym->isVirtual());
    }

    public function test_can_create_personal_training_gym(): void
    {
        $gym = new GymEntity(
            new GymId('123e4567-e89b-12d3-a456-426614174000'),
            new UserId('223e4567-e89b-12d3-a456-426614174000'),
            new GymName('Entrenamiento Personal'),
            new GymAddress('N/A'),
            new GymLocality('N/A'),
            new GymProvince('N/A'),
            new GymCountry('N/A'),
            true,
            true // is_personal_training
        );

        $this->assertTrue($gym->isPersonalTraining());
        $this->assertTrue($gym->isVirtual());
        $this->assertEquals('N/A', $gym->getAddress()->getValue());
        $this->assertEquals('N/A', $gym->getLocality()->getValue());
        $this->assertEquals('N/A', $gym->getProvince()->getValue());
        $this->assertEquals('N/A', $gym->getCountry()->getValue());
    }
}
