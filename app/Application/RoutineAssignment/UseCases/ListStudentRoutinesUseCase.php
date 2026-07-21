<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\UseCases;

use App\Application\RoutineAssignment\DTOs\GymInfoDTO;
use App\Application\RoutineAssignment\DTOs\StudentRoutineItemDTO;
use App\Application\RoutineAssignment\DTOs\StudentRoutinesResponseDTO;
use App\Application\RoutineAssignment\DTOs\TrainerInfoDTO;
use App\Domain\RoutineAssignment\Services\RoutineAssignmentCacheServiceInterface;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

final class ListStudentRoutinesUseCase
{
    private RoutineAssignmentRepositoryInterface $repository;
    private RoutineAssignmentCacheServiceInterface $cacheService;

    public function __construct(
        RoutineAssignmentRepositoryInterface $repository,
        RoutineAssignmentCacheServiceInterface $cacheService
    ) {
        $this->repository = $repository;
        $this->cacheService = $cacheService;
    }

    public function execute(string $studentId, array $filters, int $page, int $perPage): StudentRoutinesResponseDTO
    {
        $cacheParams = array_merge($filters, ['page' => $page, 'per_page' => $perPage]);

        // Intentar obtener primero desde caché
        $cached = $this->cacheService->get($studentId, $cacheParams);
        if ($cached !== null) {
            // Reconstruir DTOs desde arrays en caché
            $items = [];
            foreach ($cached['data'] as $itemArray) {
                $items[] = new StudentRoutineItemDTO(
                    id: $itemArray['id'],
                    startsAt: $itemArray['starts_at'],
                    isCurrent: $itemArray['is_current'],
                    assignedAt: $itemArray['assigned_at'],
                    routine: $itemArray['routine'],
                    gym: new GymInfoDTO(
                        id: $itemArray['gym']['id'],
                        name: $itemArray['gym']['name'],
                        isPersonalTraining: $itemArray['gym']['is_personal_training']
                    ),
                    trainer: new TrainerInfoDTO(
                        id: $itemArray['trainer']['id'],
                        name: $itemArray['trainer']['name'],
                        email: $itemArray['trainer']['email']
                    )
                );
            }
            return new StudentRoutinesResponseDTO($items, $cached['meta']);
        }

        // Consultar desde base de datos
        $result = $this->repository->findStudentRoutinesWithDetails(
            new UserId($studentId),
            $filters,
            $page,
            $perPage
        );

        // Mapear a DTOs
        $items = [];
        foreach ($result['data'] as $item) {
            $trainerFullName = trim($item->trainer_name . ' ' . $item->trainer_last_name);

            $items[] = new StudentRoutineItemDTO(
                id: $item->id,
                startsAt: $item->starts_at instanceof \DateTimeInterface ? $item->starts_at->format('Y-m-d') : $item->starts_at,
                isCurrent: (bool) $item->is_current,
                assignedAt: $item->assigned_at instanceof \DateTimeInterface ? $item->assigned_at->toISOString() : $item->assigned_at,
                routine: [
                    'id' => $item->routine_id,
                    'name' => $item->routine_name,
                    'difficulty' => $item->routine_difficulty,
                ],
                gym: new GymInfoDTO(
                    id: $item->gym_id,
                    name: $item->gym_name,
                    isPersonalTraining: (bool) $item->gym_is_personal_training
                ),
                trainer: new TrainerInfoDTO(
                    id: $item->trainer_id,
                    name: $trainerFullName,
                    email: $item->trainer_email
                )
            );
        }

        $response = new StudentRoutinesResponseDTO($items, $result['meta']);

        // Guardar en caché (convertir DTOs a arrays para almacenamiento)
        $cacheData = [];
        foreach ($items as $item) {
            $cacheData[] = $item->toArray();
        }

        $this->cacheService->set($studentId, $cacheParams, [
            'data' => $cacheData,
            'meta' => $result['meta']
        ]);

        return $response;
    }
}
