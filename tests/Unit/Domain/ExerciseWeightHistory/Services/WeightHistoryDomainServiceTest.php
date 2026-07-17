<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ExerciseWeightHistory\Services;

use App\Domain\ExerciseWeightHistory\Services\WeightHistoryDomainService;
use App\Domain\ExerciseWeightHistory\Repositories\ExerciseWeightHistoryRepositoryInterface;
use App\Domain\ExerciseWeightHistory\Entities\ExerciseWeightHistoryEntity;
use App\Domain\ExerciseWeightHistory\ValueObjects\ExerciseWeightHistoryId;
use App\Domain\ExerciseWeightHistory\ValueObjects\Weight;
use App\Domain\ExerciseWeightHistory\ValueObjects\Reps;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use PHPUnit\Framework\TestCase;
use Mockery;

class WeightHistoryDomainServiceTest extends TestCase
{
    private ExerciseWeightHistoryRepositoryInterface $repository;
    private WeightHistoryDomainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(ExerciseWeightHistoryRepositoryInterface::class);
        $this->service = new WeightHistoryDomainService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_suggested_weight_returns_weight_when_history_exists(): void
    {
        $studentId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $exerciseId = new ExerciseId('660e8400-e29b-41d4-a716-446655440000');
        $reps = new Reps(10);

        $history = new ExerciseWeightHistoryEntity(
            new ExerciseWeightHistoryId('770e8400-e29b-41d4-a716-446655440000'),
            $studentId,
            $exerciseId,
            $reps,
            new Weight(75.0),
            new \DateTimeImmutable()
        );

        $this->repository
            ->shouldReceive('findByStudentExerciseAndReps')
            ->once()
            ->with(
                Mockery::on(fn($id) => $id->equals($studentId)),
                Mockery::on(fn($id) => $id->equals($exerciseId)),
                Mockery::on(fn($r) => $r->equals($reps))
            )
            ->andReturn($history);

        $result = $this->service->getSuggestedWeight($studentId, $exerciseId, $reps);

        $this->assertNotNull($result);
        $this->assertEquals(75.0, $result->getValue());
    }

    public function test_get_suggested_weight_returns_null_when_no_history_exists(): void
    {
        $studentId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $exerciseId = new ExerciseId('660e8400-e29b-41d4-a716-446655440000');
        $reps = new Reps(10);

        $this->repository
            ->shouldReceive('findByStudentExerciseAndReps')
            ->once()
            ->andReturn(null);

        $result = $this->service->getSuggestedWeight($studentId, $exerciseId, $reps);

        $this->assertNull($result);
    }

    public function test_should_update_history_returns_true_when_weight_is_different(): void
    {
        $studentId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $exerciseId = new ExerciseId('660e8400-e29b-41d4-a716-446655440000');
        $reps = new Reps(10);
        $newWeight = new Weight(80.0);

        $history = new ExerciseWeightHistoryEntity(
            new ExerciseWeightHistoryId('770e8400-e29b-41d4-a716-446655440000'),
            $studentId,
            $exerciseId,
            $reps,
            new Weight(75.0),
            new \DateTimeImmutable()
        );

        $this->repository
            ->shouldReceive('findByStudentExerciseAndReps')
            ->once()
            ->andReturn($history);

        $result = $this->service->shouldUpdateHistory($studentId, $exerciseId, $reps, $newWeight);

        $this->assertTrue($result);
    }

    public function test_should_update_history_returns_false_when_weight_is_same(): void
    {
        $studentId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $exerciseId = new ExerciseId('660e8400-e29b-41d4-a716-446655440000');
        $reps = new Reps(10);
        $sameWeight = new Weight(75.0);

        $history = new ExerciseWeightHistoryEntity(
            new ExerciseWeightHistoryId('770e8400-e29b-41d4-a716-446655440000'),
            $studentId,
            $exerciseId,
            $reps,
            new Weight(75.0),
            new \DateTimeImmutable()
        );

        $this->repository
            ->shouldReceive('findByStudentExerciseAndReps')
            ->once()
            ->andReturn($history);

        $result = $this->service->shouldUpdateHistory($studentId, $exerciseId, $reps, $sameWeight);

        $this->assertFalse($result);
    }

    public function test_should_update_history_returns_true_when_no_history_exists(): void
    {
        $studentId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $exerciseId = new ExerciseId('660e8400-e29b-41d4-a716-446655440000');
        $reps = new Reps(10);
        $newWeight = new Weight(50.0);

        $this->repository
            ->shouldReceive('findByStudentExerciseAndReps')
            ->once()
            ->andReturn(null);

        $result = $this->service->shouldUpdateHistory($studentId, $exerciseId, $reps, $newWeight);

        $this->assertTrue($result);
    }
}
