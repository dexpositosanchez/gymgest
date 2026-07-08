<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GymStudent\Services;

use App\Domain\Gym\Entities\GymEntity;
use App\Domain\Gym\ValueObjects\GymAddress;
use App\Domain\Gym\ValueObjects\GymCountry;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\Gym\ValueObjects\GymLocality;
use App\Domain\Gym\ValueObjects\GymName;
use App\Domain\Gym\ValueObjects\GymProvince;
use App\Domain\GymStudent\Entities\GymStudentEntity;
use App\Domain\GymStudent\Services\GymStudentDomainService;
use App\Domain\GymStudent\ValueObjects\GymStudentId;
use App\Domain\GymStudent\ValueObjects\QuotaExpiresAt;
use App\Domain\User\Entities\UserEntity;
use App\Domain\User\ValueObjects\BirthDate;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\Gender;
use App\Domain\User\ValueObjects\GymGoals;
use App\Domain\User\ValueObjects\Password;
use App\Domain\User\ValueObjects\PersonName;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserType;
use PHPUnit\Framework\TestCase;

class GymStudentDomainServiceTest extends TestCase
{
    private GymStudentDomainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GymStudentDomainService();
    }

    private function createGym(): GymEntity
    {
        return new GymEntity(
            new GymId('123e4567-e89b-12d3-a456-426614174000'),
            new UserId('223e4567-e89b-12d3-a456-426614174000'),
            new GymName('Test Gym'),
            new GymAddress('Test Address'),
            new GymLocality('Madrid'),
            new GymProvince('Comunidad de Madrid'),
            new GymCountry('España'),
            true
        );
    }

    private function createStudent(): UserEntity
    {
        return new UserEntity(
            new UserId('323e4567-e89b-12d3-a456-426614174000'),
            new Email('student@test.com'),
            Password::fromHashed('$2y$10$fake.hash.for.testing.purposes.only'),
            new UserType('student'),
            new PersonName('Student'),
            new PersonName('Name'),
            new BirthDate('2000-01-01'),
            new Gender('other'),
            new GymGoals('fitness')
        );
    }

    private function createTrainer(): UserEntity
    {
        return new UserEntity(
            new UserId('423e4567-e89b-12d3-a456-426614174000'),
            new Email('trainer@test.com'),
            Password::fromHashed('$2y$10$fake.hash.for.testing.purposes.only'),
            new UserType('trainer'),
            new PersonName('Trainer'),
            new PersonName('Name'),
            new BirthDate('1990-01-01'),
            new Gender('other'),
            new GymGoals('fitness')
        );
    }

    private function createGymStudent(string $quotaDate = '+30 days', bool $isActive = true): GymStudentEntity
    {
        return new GymStudentEntity(
            new GymStudentId('523e4567-e89b-12d3-a456-426614174000'),
            new GymId('123e4567-e89b-12d3-a456-426614174000'),
            new UserId('323e4567-e89b-12d3-a456-426614174000'),
            new QuotaExpiresAt(date('Y-m-d', strtotime($quotaDate))),
            $isActive
        );
    }

    public function test_can_enroll_when_user_is_student(): void
    {
        $gym = $this->createGym();
        $student = $this->createStudent();

        $result = $this->service->canEnroll($gym, $student);

        $this->assertTrue($result);
    }

    public function test_cannot_enroll_when_user_is_not_student(): void
    {
        $gym = $this->createGym();
        $trainer = $this->createTrainer();

        $result = $this->service->canEnroll($gym, $trainer);

        $this->assertFalse($result);
    }

    public function test_get_quota_status_returns_active_for_valid_quota(): void
    {
        $gymStudent = $this->createGymStudent('+30 days', true);

        $status = $this->service->getQuotaStatus($gymStudent);

        $this->assertEquals('active', $status);
    }

    public function test_get_quota_status_returns_expiring_soon_for_quota_within_7_days(): void
    {
        $gymStudent = $this->createGymStudent('+5 days', true);

        $status = $this->service->getQuotaStatus($gymStudent);

        $this->assertEquals('expiring_soon', $status);
    }

    public function test_get_quota_status_returns_expired_for_past_quota(): void
    {
        $gymStudent = $this->createGymStudent('-5 days', true);

        $status = $this->service->getQuotaStatus($gymStudent);

        $this->assertEquals('expired', $status);
    }

    public function test_get_quota_status_returns_inactive_for_deactivated_student(): void
    {
        $gymStudent = $this->createGymStudent('+30 days', false);

        $status = $this->service->getQuotaStatus($gymStudent);

        $this->assertEquals('inactive', $status);
    }

    public function test_get_quota_status_priority_inactive_over_expired(): void
    {
        $gymStudent = $this->createGymStudent('-10 days', false);

        $status = $this->service->getQuotaStatus($gymStudent);

        $this->assertEquals('inactive', $status);
    }
}
