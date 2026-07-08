<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Gym\Services;

use App\Domain\Gym\Entities\GymEntity;
use App\Domain\Gym\Services\GymDomainService;
use App\Domain\Gym\ValueObjects\GymAddress;
use App\Domain\Gym\ValueObjects\GymLocality;
use App\Domain\Gym\ValueObjects\GymProvince;
use App\Domain\Gym\ValueObjects\GymCountry;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\Gym\ValueObjects\GymName;
use App\Domain\User\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

class GymDomainServiceTest extends TestCase
{
    private GymDomainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GymDomainService();
    }

    private function createGym(string $trainerId = '223e4567-e89b-12d3-a456-426614174000'): GymEntity
    {
        return new GymEntity(
            new GymId('123e4567-e89b-12d3-a456-426614174000'),
            new UserId($trainerId),
            new GymName('FitZone Madrid Centro'),
            new GymAddress('Calle Gran Vía, 123'),
            new GymLocality('Madrid'),
            new GymProvince('Comunidad de Madrid'),
            new GymCountry('España'),
            true
        );
    }

    public function test_trainer_can_modify_own_gym(): void
    {
        $trainerId = new UserId('223e4567-e89b-12d3-a456-426614174000');
        $gym = $this->createGym('223e4567-e89b-12d3-a456-426614174000');

        $result = $this->service->canTrainerModify($gym, $trainerId);

        $this->assertTrue($result);
    }

    public function test_trainer_cannot_modify_other_trainers_gym(): void
    {
        $trainerId = new UserId('323e4567-e89b-12d3-a456-426614174000');
        $gym = $this->createGym('423e4567-e89b-12d3-a456-426614174000');

        $result = $this->service->canTrainerModify($gym, $trainerId);

        $this->assertFalse($result);
    }

    public function test_is_assigned_returns_false(): void
    {
        $gym = $this->createGym();

        $result = $this->service->isAssigned($gym);

        $this->assertFalse($result);
    }
}
