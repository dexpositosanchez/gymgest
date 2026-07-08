<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GymStudent\Entities;

use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\GymStudent\Entities\GymStudentEntity;
use App\Domain\GymStudent\ValueObjects\GymStudentId;
use App\Domain\GymStudent\ValueObjects\QuotaExpiresAt;
use App\Domain\User\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

class GymStudentEntityTest extends TestCase
{
    private function createGymStudent(bool $isActive = true, string $quotaDate = '+30 days'): GymStudentEntity
    {
        return new GymStudentEntity(
            new GymStudentId('123e4567-e89b-12d3-a456-426614174000'),
            new GymId('223e4567-e89b-12d3-a456-426614174000'),
            new UserId('323e4567-e89b-12d3-a456-426614174000'),
            new QuotaExpiresAt(date('Y-m-d', strtotime($quotaDate))),
            $isActive
        );
    }

    public function test_can_create_gym_student(): void
    {
        $gymStudent = $this->createGymStudent();

        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $gymStudent->getId()->getValue());
        $this->assertEquals('223e4567-e89b-12d3-a456-426614174000', $gymStudent->getGymId()->getValue());
        $this->assertEquals('323e4567-e89b-12d3-a456-426614174000', $gymStudent->getStudentId()->getValue());
        $this->assertTrue($gymStudent->isActive());
    }

    public function test_can_update_quota_expires_at(): void
    {
        $gymStudent = $this->createGymStudent();
        $newDate = new QuotaExpiresAt(date('Y-m-d', strtotime('+60 days')));

        $gymStudent->updateQuotaExpiresAt($newDate);

        $this->assertEquals($newDate->getValue(), $gymStudent->getQuotaExpiresAt()->getValue());
    }

    public function test_can_deactivate(): void
    {
        $gymStudent = $this->createGymStudent(true);

        $gymStudent->deactivate();

        $this->assertFalse($gymStudent->isActive());
    }

    public function test_can_reactivate_with_new_quota(): void
    {
        $gymStudent = $this->createGymStudent(false);
        $newQuota = new QuotaExpiresAt(date('Y-m-d', strtotime('+30 days')));

        $gymStudent->reactivate($newQuota);

        $this->assertTrue($gymStudent->isActive());
        $this->assertEquals($newQuota->getValue(), $gymStudent->getQuotaExpiresAt()->getValue());
    }

    public function test_belongs_to_gym(): void
    {
        $gymId = new GymId('223e4567-e89b-12d3-a456-426614174000');
        $gymStudent = $this->createGymStudent();

        $this->assertTrue($gymStudent->belongsToGym($gymId));
    }

    public function test_does_not_belong_to_different_gym(): void
    {
        $differentGymId = new GymId('999e4567-e89b-12d3-a456-426614174000');
        $gymStudent = $this->createGymStudent();

        $this->assertFalse($gymStudent->belongsToGym($differentGymId));
    }

    public function test_is_student(): void
    {
        $studentId = new UserId('323e4567-e89b-12d3-a456-426614174000');
        $gymStudent = $this->createGymStudent();

        $this->assertTrue($gymStudent->isStudent($studentId));
    }

    public function test_is_not_different_student(): void
    {
        $differentStudentId = new UserId('999e4567-e89b-12d3-a456-426614174000');
        $gymStudent = $this->createGymStudent();

        $this->assertFalse($gymStudent->isStudent($differentStudentId));
    }
}
