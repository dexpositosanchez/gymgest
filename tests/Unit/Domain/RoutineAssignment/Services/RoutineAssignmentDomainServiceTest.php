<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\RoutineAssignment\Services;

use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\RoutineAssignment\Entities\RoutineAssignmentEntity;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\RoutineAssignment\Services\RoutineAssignmentDomainService;
use App\Domain\RoutineAssignment\ValueObjects\AssignedAt;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use App\Domain\RoutineAssignment\ValueObjects\StartsAt;
use App\Domain\User\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

class RoutineAssignmentDomainServiceTest extends TestCase
{
    private function createMockRepository(): RoutineAssignmentRepositoryInterface
    {
        return $this->createMock(RoutineAssignmentRepositoryInterface::class);
    }

    private function createAssignment(
        string $assignmentId,
        string $studentId,
        string $gymId,
        bool $isCurrent
    ): RoutineAssignmentEntity {
        return new RoutineAssignmentEntity(
            new RoutineAssignmentId($assignmentId),
            new RoutineId('999e4567-e89b-12d3-a456-426614174000'),
            new UserId($studentId),
            new GymId($gymId),
            AssignedAt::now(),
            StartsAt::fromString(date('Y-m-d')),
            $isCurrent,
            null
        );
    }

    public function test_set_current_routine_sets_one_as_current(): void
    {
        $repository = $this->createMockRepository();
        $service = new RoutineAssignmentDomainService($repository);

        $studentId = new UserId('111e4567-e89b-12d3-a456-426614174001');
        $gymId = new GymId('222e4567-e89b-12d3-a456-426614174001');
        $assignmentId = new RoutineAssignmentId('333e4567-e89b-12d3-a456-426614174001');

        $targetAssignment = $this->createAssignment(
            '333e4567-e89b-12d3-a456-426614174001',
            '111e4567-e89b-12d3-a456-426614174001',
            '222e4567-e89b-12d3-a456-426614174001',
            false
        );

        $repository->expects($this->once())
            ->method('findById')
            ->with($assignmentId)
            ->willReturn($targetAssignment);

        $repository->expects($this->once())
            ->method('findByStudentAndGym')
            ->with($studentId, $gymId)
            ->willReturn([$targetAssignment]);

        $repository->expects($this->exactly(2))
            ->method('save')
            ->with($targetAssignment);

        $service->setCurrentRoutine($studentId, $gymId, $assignmentId);

        $this->assertTrue($targetAssignment->isCurrent());
    }

    public function test_set_current_routine_unsets_others(): void
    {
        $repository = $this->createMockRepository();
        $service = new RoutineAssignmentDomainService($repository);

        $studentId = new UserId('111e4567-e89b-12d3-a456-426614174002');
        $gymId = new GymId('222e4567-e89b-12d3-a456-426614174002');
        $assignmentId = new RoutineAssignmentId('333e4567-e89b-12d3-a456-426614174002');

        $oldCurrentAssignment = $this->createAssignment(
            '444e4567-e89b-12d3-a456-426614174002',
            '111e4567-e89b-12d3-a456-426614174002',
            '222e4567-e89b-12d3-a456-426614174002',
            true
        );

        $newCurrentAssignment = $this->createAssignment(
            '333e4567-e89b-12d3-a456-426614174002',
            '111e4567-e89b-12d3-a456-426614174002',
            '222e4567-e89b-12d3-a456-426614174002',
            false
        );

        $repository->expects($this->once())
            ->method('findById')
            ->with($assignmentId)
            ->willReturn($newCurrentAssignment);

        $repository->expects($this->once())
            ->method('findByStudentAndGym')
            ->with($studentId, $gymId)
            ->willReturn([$oldCurrentAssignment, $newCurrentAssignment]);

        $repository->expects($this->exactly(3))
            ->method('save');

        $service->setCurrentRoutine($studentId, $gymId, $assignmentId);

        $this->assertFalse($oldCurrentAssignment->isCurrent());
        $this->assertTrue($newCurrentAssignment->isCurrent());
    }

    public function test_set_current_routine_isolates_by_student_and_gym(): void
    {
        $repository = $this->createMockRepository();
        $service = new RoutineAssignmentDomainService($repository);

        $studentId = new UserId('111e4567-e89b-12d3-a456-426614174003');
        $gymId = new GymId('222e4567-e89b-12d3-a456-426614174003');
        $assignmentId = new RoutineAssignmentId('333e4567-e89b-12d3-a456-426614174003');

        $assignmentStudent1Gym1 = $this->createAssignment(
            '333e4567-e89b-12d3-a456-426614174003',
            '111e4567-e89b-12d3-a456-426614174003',
            '222e4567-e89b-12d3-a456-426614174003',
            false
        );

        // Assignment from different student or gym should not be affected
        $repository->expects($this->once())
            ->method('findById')
            ->with($assignmentId)
            ->willReturn($assignmentStudent1Gym1);

        $repository->expects($this->once())
            ->method('findByStudentAndGym')
            ->with($studentId, $gymId)
            ->willReturn([$assignmentStudent1Gym1]);

        $repository->expects($this->exactly(2))
            ->method('save')
            ->with($assignmentStudent1Gym1);

        $service->setCurrentRoutine($studentId, $gymId, $assignmentId);

        $this->assertTrue($assignmentStudent1Gym1->isCurrent());
    }
}
