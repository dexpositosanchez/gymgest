<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exercise\Services;

use App\Domain\Exercise\Entities\ExerciseEntity;
use App\Domain\Exercise\Services\ExerciseDomainService;
use App\Domain\Exercise\ValueObjects\ExerciseDescription;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\Exercise\ValueObjects\ExerciseName;
use App\Domain\Exercise\ValueObjects\ExerciseType;
use App\Domain\Exercise\ValueObjects\MuscleGroupId;
use App\Domain\User\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

class ExerciseDomainServiceTest extends TestCase
{
    private ExerciseDomainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExerciseDomainService();
    }

    private function createDefaultExercise(): ExerciseEntity
    {
        return new ExerciseEntity(
            new ExerciseId('123e4567-e89b-12d3-a456-426614174000'),
            new ExerciseName('Press de banca'),
            new ExerciseDescription('Fundamental exercise for chest development'),
            new MuscleGroupId('223e4567-e89b-12d3-a456-426614174000'),
            ExerciseType::default()
        );
    }

    private function createCustomExercise(string $trainerId = '323e4567-e89b-12d3-a456-426614174000'): ExerciseEntity
    {
        return new ExerciseEntity(
            new ExerciseId('123e4567-e89b-12d3-a456-426614174000'),
            new ExerciseName('Custom exercise'),
            new ExerciseDescription('My custom exercise description'),
            new MuscleGroupId('223e4567-e89b-12d3-a456-426614174000'),
            ExerciseType::custom(),
            new UserId($trainerId)
        );
    }

    public function test_trainer_can_edit_own_custom_exercise(): void
    {
        $trainerId = new UserId('323e4567-e89b-12d3-a456-426614174000');
        $exercise = $this->createCustomExercise('323e4567-e89b-12d3-a456-426614174000');

        $result = $this->service->canTrainerEditExercise($exercise, $trainerId);

        $this->assertTrue($result);
    }

    public function test_trainer_cannot_edit_other_trainers_exercise(): void
    {
        $trainerId = new UserId('423e4567-e89b-12d3-a456-426614174000');
        $exercise = $this->createCustomExercise('523e4567-e89b-12d3-a456-426614174000');

        $result = $this->service->canTrainerEditExercise($exercise, $trainerId);

        $this->assertFalse($result);
    }

    public function test_trainer_cannot_edit_default_exercise(): void
    {
        $trainerId = new UserId('323e4567-e89b-12d3-a456-426614174000');
        $exercise = $this->createDefaultExercise();

        $result = $this->service->canTrainerEditExercise($exercise, $trainerId);

        $this->assertFalse($result);
    }

    public function test_trainer_can_delete_own_custom_exercise(): void
    {
        $trainerId = new UserId('323e4567-e89b-12d3-a456-426614174000');
        $exercise = $this->createCustomExercise('323e4567-e89b-12d3-a456-426614174000');

        $result = $this->service->canTrainerDeleteExercise($exercise, $trainerId);

        $this->assertTrue($result);
    }

    public function test_trainer_cannot_delete_other_trainers_exercise(): void
    {
        $trainerId = new UserId('423e4567-e89b-12d3-a456-426614174000');
        $exercise = $this->createCustomExercise('523e4567-e89b-12d3-a456-426614174000');

        $result = $this->service->canTrainerDeleteExercise($exercise, $trainerId);

        $this->assertFalse($result);
    }

    public function test_trainer_cannot_delete_default_exercise(): void
    {
        $trainerId = new UserId('323e4567-e89b-12d3-a456-426614174000');
        $exercise = $this->createDefaultExercise();

        $result = $this->service->canTrainerDeleteExercise($exercise, $trainerId);

        $this->assertFalse($result);
    }

    public function test_trainer_can_toggle_default_exercise(): void
    {
        $trainerId = new UserId('323e4567-e89b-12d3-a456-426614174000');
        $exercise = $this->createDefaultExercise();

        $result = $this->service->canTrainerToggleExercise($exercise, $trainerId);

        $this->assertTrue($result);
    }

    public function test_trainer_cannot_toggle_custom_exercise(): void
    {
        $trainerId = new UserId('323e4567-e89b-12d3-a456-426614174000');
        $exercise = $this->createCustomExercise('323e4567-e89b-12d3-a456-426614174000');

        $result = $this->service->canTrainerToggleExercise($exercise, $trainerId);

        $this->assertFalse($result);
    }
}
