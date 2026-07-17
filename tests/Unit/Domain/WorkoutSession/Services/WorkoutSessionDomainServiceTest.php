<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\WorkoutSession\Services;

use App\Domain\WorkoutSession\Services\WorkoutSessionDomainService;
use App\Domain\WorkoutSession\Repositories\WorkoutSessionRepositoryInterface;
use App\Domain\SetExecution\Repositories\SetExecutionRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use PHPUnit\Framework\TestCase;
use Mockery;

class WorkoutSessionDomainServiceTest extends TestCase
{
    private WorkoutSessionRepositoryInterface $sessionRepository;
    private SetExecutionRepositoryInterface $setExecutionRepository;
    private WorkoutSessionDomainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionRepository = Mockery::mock(WorkoutSessionRepositoryInterface::class);
        $this->setExecutionRepository = Mockery::mock(SetExecutionRepositoryInterface::class);
        $this->service = new WorkoutSessionDomainService(
            $this->sessionRepository,
            $this->setExecutionRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_start_new_session_when_no_active_session_exists(): void
    {
        $studentId = new UserId('550e8400-e29b-41d4-a716-446655440000');

        $this->sessionRepository
            ->shouldReceive('findActiveByStudent')
            ->once()
            ->with(Mockery::on(fn($id) => $id->equals($studentId)))
            ->andReturn(null);

        $result = $this->service->canStartNewSession($studentId);

        $this->assertTrue($result);
    }

    public function test_cannot_start_new_session_when_active_session_exists(): void
    {
        $studentId = new UserId('550e8400-e29b-41d4-a716-446655440000');

        $this->sessionRepository
            ->shouldReceive('findActiveByStudent')
            ->once()
            ->with(Mockery::on(fn($id) => $id->equals($studentId)))
            ->andReturn(Mockery::mock('App\Domain\WorkoutSession\Entities\WorkoutSessionEntity'));

        $result = $this->service->canStartNewSession($studentId);

        $this->assertFalse($result);
    }

    public function test_exercise_is_completed_when_all_sets_are_executed(): void
    {
        $sessionId = new WorkoutSessionId('550e8400-e29b-41d4-a716-446655440000');
        $exerciseId = new ExerciseId('660e8400-e29b-41d4-a716-446655440000');

        // Total sets: 3, Completed sets: 3
        $this->setExecutionRepository
            ->shouldReceive('countCompletedSets')
            ->once()
            ->with(
                Mockery::on(fn($id) => $id->equals($sessionId)),
                Mockery::on(fn($id) => $id->equals($exerciseId))
            )
            ->andReturn(3);

        $result = $this->service->isExerciseCompleted($sessionId, $exerciseId, 3);

        $this->assertTrue($result);
    }

    public function test_exercise_is_not_completed_when_not_all_sets_are_executed(): void
    {
        $sessionId = new WorkoutSessionId('550e8400-e29b-41d4-a716-446655440000');
        $exerciseId = new ExerciseId('660e8400-e29b-41d4-a716-446655440000');

        // Total sets: 3, Completed sets: 2
        $this->setExecutionRepository
            ->shouldReceive('countCompletedSets')
            ->once()
            ->with(
                Mockery::on(fn($id) => $id->equals($sessionId)),
                Mockery::on(fn($id) => $id->equals($exerciseId))
            )
            ->andReturn(2);

        $result = $this->service->isExerciseCompleted($sessionId, $exerciseId, 3);

        $this->assertFalse($result);
    }

    public function test_exercise_is_not_completed_when_no_sets_executed(): void
    {
        $sessionId = new WorkoutSessionId('550e8400-e29b-41d4-a716-446655440000');
        $exerciseId = new ExerciseId('660e8400-e29b-41d4-a716-446655440000');

        $this->setExecutionRepository
            ->shouldReceive('countCompletedSets')
            ->once()
            ->andReturn(0);

        $result = $this->service->isExerciseCompleted($sessionId, $exerciseId, 4);

        $this->assertFalse($result);
    }
}
