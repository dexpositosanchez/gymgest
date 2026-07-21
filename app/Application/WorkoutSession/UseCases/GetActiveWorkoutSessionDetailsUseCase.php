<?php

declare(strict_types=1);

namespace App\Application\WorkoutSession\UseCases;

use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\Routine\Repositories\RoutineDayExerciseRepositoryInterface;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\SetExecution\Repositories\SetExecutionRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\WorkoutSession\Repositories\WorkoutSessionExerciseStatusRepositoryInterface;
use App\Domain\WorkoutSession\Repositories\WorkoutSessionRepositoryInterface;

class GetActiveWorkoutSessionDetailsUseCase
{
    /** @var WorkoutSessionRepositoryInterface */
    private $sessionRepository;

    /** @var RoutineAssignmentRepositoryInterface */
    private $assignmentRepository;

    /** @var RoutineDayExerciseRepositoryInterface */
    private $routineDayExerciseRepository;

    /** @var SetExecutionRepositoryInterface */
    private $setExecutionRepository;

    /** @var WorkoutSessionExerciseStatusRepositoryInterface */
    private $exerciseStatusRepository;

    public function __construct(
        WorkoutSessionRepositoryInterface $sessionRepository,
        RoutineAssignmentRepositoryInterface $assignmentRepository,
        RoutineDayExerciseRepositoryInterface $routineDayExerciseRepository,
        SetExecutionRepositoryInterface $setExecutionRepository,
        WorkoutSessionExerciseStatusRepositoryInterface $exerciseStatusRepository
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->assignmentRepository = $assignmentRepository;
        $this->routineDayExerciseRepository = $routineDayExerciseRepository;
        $this->setExecutionRepository = $setExecutionRepository;
        $this->exerciseStatusRepository = $exerciseStatusRepository;
    }

    /**
     * Obtener detalles de sesión de entrenamiento activa para un estudiante
     *
     * @param string $studentId
     * @return array|null Array con detalles de la sesión o null si no hay sesión activa
     */
    public function execute(string $studentId): ?array
    {
        $studentIdVO = new UserId($studentId);

        // Buscar sesión activa
        $activeSession = $this->sessionRepository->findActiveByStudent($studentIdVO);

        if ($activeSession === null) {
            return null;
        }

        // Obtener asignación de rutina
        $assignment = $this->assignmentRepository->findById($activeSession->getRoutineAssignmentId());

        if ($assignment === null) {
            throw new \DomainException('Asignación de rutina no encontrada');
        }

        // Obtener ejercicios con detalles (enfoque pragmático - consulta única con joins)
        $exercisesData = $this->routineDayExerciseRepository->getExercisesWithDetailsForDay(
            $assignment->getRoutineId(),
            $activeSession->getDayNumber()
        );

        // Enriquecer con datos de ejecución
        $exercises = [];
        foreach ($exercisesData as $exerciseData) {
            $exerciseId = new ExerciseId($exerciseData['exercise_id']);
            $sessionId = $activeSession->getId();

            // Contar series completadas
            $completedSets = $this->setExecutionRepository->countBySessionAndExercise($sessionId, $exerciseId);

            // Verificar si el ejercicio está marcado como completado
            $isCompleted = $this->exerciseStatusRepository->isExerciseCompleted($sessionId, $exerciseId);

            $exercises[] = [
                'exercise_id' => $exerciseData['exercise_id'],
                'name' => $exerciseData['exercise_name'],
                'total_sets' => $exerciseData['total_sets'],
                'completed_sets' => $completedSets,
                'is_completed' => $isCompleted,
            ];
        }

        return [
            'id' => $activeSession->getId()->getValue(),
            'day_number' => $activeSession->getDayNumber()->getValue(),
            'started_at' => $activeSession->getStartedAt()->format('Y-m-d H:i:s'),
            'exercises' => $exercises,
        ];
    }
}
