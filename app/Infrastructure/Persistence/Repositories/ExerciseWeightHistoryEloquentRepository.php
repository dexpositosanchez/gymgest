<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\ExerciseWeightHistory\Entities\ExerciseWeightHistoryEntity;
use App\Domain\ExerciseWeightHistory\Repositories\ExerciseWeightHistoryRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\ExerciseWeightHistory\ValueObjects\Reps;
use App\Infrastructure\Persistence\Eloquent\ExerciseWeightHistoryEloquentModel;
use App\Infrastructure\Persistence\Mappers\ExerciseWeightHistoryMapper;
use Illuminate\Support\Facades\DB;

class ExerciseWeightHistoryEloquentRepository implements ExerciseWeightHistoryRepositoryInterface
{
    public function upsert(ExerciseWeightHistoryEntity $history): void
    {
        // Using raw query with updateOrInsert for UPSERT behavior
        DB::table('exercise_weight_history')->updateOrInsert(
            [
                'student_id' => $history->getStudentId()->getValue(),
                'exercise_id' => $history->getExerciseId()->getValue(),
                'reps' => $history->getReps()->getValue(),
            ],
            [
                'id' => $history->getId()->getValue(),
                'weight' => $history->getWeight()->getValue(),
                'last_used_at' => $history->getLastUsedAt()->format('Y-m-d H:i:s'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function findByStudentExerciseAndReps(
        UserId $studentId,
        ExerciseId $exerciseId,
        Reps $reps
    ): ?ExerciseWeightHistoryEntity {
        $model = ExerciseWeightHistoryEloquentModel::where('student_id', $studentId->getValue())
            ->where('exercise_id', $exerciseId->getValue())
            ->where('reps', $reps->getValue())
            ->first();

        if ($model === null) {
            return null;
        }

        return ExerciseWeightHistoryMapper::toDomain($model);
    }

    public function findSuggestedWeight(UserId $studentId, ExerciseId $exerciseId, Reps $reps): ?float
    {
        $record = ExerciseWeightHistoryEloquentModel::where('student_id', $studentId->getValue())
            ->where('exercise_id', $exerciseId->getValue())
            ->where('reps', $reps->getValue())
            ->orderBy('last_used_at', 'desc')
            ->first();

        return $record ? $record->weight : null;
    }
}
