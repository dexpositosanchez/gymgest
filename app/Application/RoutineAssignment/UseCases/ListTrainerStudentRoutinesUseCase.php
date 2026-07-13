<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\UseCases;

use App\Application\RoutineAssignment\Services\RoutineAssignmentResponseBuilderInterface;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

class ListTrainerStudentRoutinesUseCase
{
    private RoutineAssignmentRepositoryInterface $assignmentRepository;
    private RoutineAssignmentResponseBuilderInterface $responseBuilder;

    public function __construct(
        RoutineAssignmentRepositoryInterface $assignmentRepository,
        RoutineAssignmentResponseBuilderInterface $responseBuilder
    ) {
        $this->assignmentRepository = $assignmentRepository;
        $this->responseBuilder = $responseBuilder;
    }

    public function execute(string $studentId, string $gymId): array
    {
        $assignments = $this->assignmentRepository->findByStudentAndGym(
            new UserId($studentId),
            new GymId($gymId)
        );

        $result = [];
        foreach ($assignments as $assignment) {
            $result[] = $this->responseBuilder->buildFromEntity($assignment);
        }

        return $result;
    }
}
