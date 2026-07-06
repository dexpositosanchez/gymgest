<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exercise\Entities;

use App\Domain\Exercise\Entities\ExerciseEntity;
use App\Domain\Exercise\ValueObjects\ExerciseDescription;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\Exercise\ValueObjects\ExerciseName;
use App\Domain\Exercise\ValueObjects\ExerciseType;
use App\Domain\Exercise\ValueObjects\MuscleGroupId;
use App\Domain\User\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

class ExerciseEntityTest extends TestCase
{
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

    private function createCustomExercise(): ExerciseEntity
    {
        return new ExerciseEntity(
            new ExerciseId('123e4567-e89b-12d3-a456-426614174000'),
            new ExerciseName('Custom exercise'),
            new ExerciseDescription('My custom exercise description'),
            new MuscleGroupId('223e4567-e89b-12d3-a456-426614174000'),
            ExerciseType::custom(),
            new UserId('323e4567-e89b-12d3-a456-426614174000')
        );
    }

    public function test_can_create_default_exercise(): void
    {
        $exercise = $this->createDefaultExercise();

        $this->assertTrue($exercise->isDefault());
        $this->assertFalse($exercise->isCustom());
        $this->assertNull($exercise->getTrainerId());
    }

    public function test_can_create_custom_exercise(): void
    {
        $exercise = $this->createCustomExercise();

        $this->assertTrue($exercise->isCustom());
        $this->assertFalse($exercise->isDefault());
        $this->assertNotNull($exercise->getTrainerId());
    }

    public function test_default_exercise_is_not_editable(): void
    {
        $exercise = $this->createDefaultExercise();

        $this->assertFalse($exercise->isEditable());
    }

    public function test_custom_exercise_with_trainer_is_editable(): void
    {
        $exercise = $this->createCustomExercise();

        $this->assertTrue($exercise->isEditable());
    }

    public function test_default_exercise_is_not_deletable(): void
    {
        $exercise = $this->createDefaultExercise();

        $this->assertFalse($exercise->isDeletable());
    }

    public function test_custom_exercise_with_trainer_is_deletable(): void
    {
        $exercise = $this->createCustomExercise();

        $this->assertTrue($exercise->isDeletable());
    }

    public function test_default_exercise_can_be_toggled(): void
    {
        $exercise = $this->createDefaultExercise();

        $this->assertTrue($exercise->canBeToggled());
    }

    public function test_custom_exercise_cannot_be_toggled(): void
    {
        $exercise = $this->createCustomExercise();

        $this->assertFalse($exercise->canBeToggled());
    }

    public function test_exercise_belongs_to_trainer(): void
    {
        $trainerId = new UserId('323e4567-e89b-12d3-a456-426614174000');
        $exercise = $this->createCustomExercise();

        $this->assertTrue($exercise->belongsToTrainer($trainerId));
    }

    public function test_exercise_does_not_belong_to_different_trainer(): void
    {
        $differentTrainerId = new UserId('423e4567-e89b-12d3-a456-426614174000');
        $exercise = $this->createCustomExercise();

        $this->assertFalse($exercise->belongsToTrainer($differentTrainerId));
    }

    public function test_default_exercise_does_not_belong_to_any_trainer(): void
    {
        $trainerId = new UserId('323e4567-e89b-12d3-a456-426614174000');
        $exercise = $this->createDefaultExercise();

        $this->assertFalse($exercise->belongsToTrainer($trainerId));
    }

    public function test_can_update_exercise_name(): void
    {
        $exercise = $this->createCustomExercise();
        $newName = new ExerciseName('Updated exercise name');

        $exercise->updateName($newName);

        $this->assertEquals($newName->getValue(), $exercise->getName()->getValue());
    }

    public function test_can_update_exercise_description(): void
    {
        $exercise = $this->createCustomExercise();
        $newDescription = new ExerciseDescription('Updated description for exercise');

        $exercise->updateDescription($newDescription);

        $this->assertEquals($newDescription->getValue(), $exercise->getDescription()->getValue());
    }

    public function test_can_update_muscle_group(): void
    {
        $exercise = $this->createCustomExercise();
        $newMuscleGroupId = new MuscleGroupId('523e4567-e89b-12d3-a456-426614174000');

        $exercise->updateMuscleGroup($newMuscleGroupId);

        $this->assertEquals($newMuscleGroupId->getValue(), $exercise->getMuscleGroupId()->getValue());
    }
}
