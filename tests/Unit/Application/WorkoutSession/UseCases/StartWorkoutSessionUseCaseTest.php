<?php

declare(strict_types=1);

namespace Tests\Unit\Application\WorkoutSession\UseCases;

use App\Application\WorkoutSession\UseCases\StartWorkoutSessionUseCase;
use App\Domain\WorkoutSession\Services\WorkoutSessionDomainService;
use App\Domain\WorkoutSession\Repositories\WorkoutSessionRepositoryInterface;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\Routine\Repositories\RoutineRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;
use Mockery;

class StartWorkoutSessionUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_throws_exception_when_student_has_active_session(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Ya tienes una sesión activa');

        $domainService = Mockery::mock(WorkoutSessionDomainService::class);
        $domainService->shouldReceive('canStartNewSession')
            ->once()
            ->andReturn(false);

        $sessionRepo = Mockery::mock(WorkoutSessionRepositoryInterface::class);
        $assignmentRepo = Mockery::mock(RoutineAssignmentRepositoryInterface::class);
        $routineRepo = Mockery::mock(RoutineRepositoryInterface::class);

        $useCase = new StartWorkoutSessionUseCase(
            $domainService,
            $sessionRepo,
            $assignmentRepo,
            $routineRepo
        );

        $useCase->execute(
            '550e8400-e29b-41d4-a716-446655440000', // studentId
            '660e8400-e29b-41d4-a716-446655440000', // routineAssignmentId
            1, // dayNumber
            null
        );
    }

    public function test_throws_exception_when_routine_assignment_not_found(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Rutina no asignada');

        $domainService = Mockery::mock(WorkoutSessionDomainService::class);
        $domainService->shouldReceive('canStartNewSession')->andReturn(true);

        $sessionRepo = Mockery::mock(WorkoutSessionRepositoryInterface::class);
        $assignmentRepo = Mockery::mock(RoutineAssignmentRepositoryInterface::class);
        $assignmentRepo->shouldReceive('findById')->andReturn(null);

        $routineRepo = Mockery::mock(RoutineRepositoryInterface::class);

        $useCase = new StartWorkoutSessionUseCase(
            $domainService,
            $sessionRepo,
            $assignmentRepo,
            $routineRepo
        );

        $useCase->execute(
            '550e8400-e29b-41d4-a716-446655440000',
            '660e8400-e29b-41d4-a716-446655440000',
            1,
            null
        );
    }

    public function test_throws_exception_when_day_number_does_not_exist_in_routine(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('El día 5 no existe en esta rutina');

        $domainService = Mockery::mock(WorkoutSessionDomainService::class);
        $domainService->shouldReceive('canStartNewSession')->andReturn(true);

        $sessionRepo = Mockery::mock(WorkoutSessionRepositoryInterface::class);

        $assignment = Mockery::mock('App\Domain\RoutineAssignment\Entities\RoutineAssignmentEntity');
        $assignment->shouldReceive('getStudentId')->andReturn(new UserId('550e8400-e29b-41d4-a716-446655440000'));
        $assignment->shouldReceive('getRoutineId')->andReturn(Mockery::mock('App\Domain\Routine\ValueObjects\RoutineId'));

        $assignmentRepo = Mockery::mock(RoutineAssignmentRepositoryInterface::class);
        $assignmentRepo->shouldReceive('findById')->andReturn($assignment);

        $routine = Mockery::mock('App\Domain\Routine\Entities\RoutineEntity');
        $routine->shouldReceive('hasDayNumber')->with(5)->andReturn(false);

        $routineRepo = Mockery::mock(RoutineRepositoryInterface::class);
        $routineRepo->shouldReceive('findById')->andReturn($routine);

        $useCase = new StartWorkoutSessionUseCase(
            $domainService,
            $sessionRepo,
            $assignmentRepo,
            $routineRepo
        );

        $useCase->execute(
            '550e8400-e29b-41d4-a716-446655440000',
            '660e8400-e29b-41d4-a716-446655440000',
            5, // Invalid day
            null
        );
    }
}
