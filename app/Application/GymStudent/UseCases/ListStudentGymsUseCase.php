<?php

declare(strict_types=1);

namespace App\Application\GymStudent\UseCases;

use App\Application\GymStudent\DTOs\StudentGymItemDTO;
use App\Domain\RoutineAssignment\Services\RoutineAssignmentCacheServiceInterface;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

final class ListStudentGymsUseCase
{
    private GymStudentRepositoryInterface $repository;
    private RoutineAssignmentCacheServiceInterface $cacheService;

    public function __construct(
        GymStudentRepositoryInterface $repository,
        RoutineAssignmentCacheServiceInterface $cacheService
    ) {
        $this->repository = $repository;
        $this->cacheService = $cacheService;
    }

    public function execute(string $studentId): array
    {
        // Intentar obtener primero desde caché
        $cached = $this->cacheService->get($studentId, ['type' => 'gyms']);

        if ($cached !== null) {
            return $cached;
        }

        // Consultar desde base de datos
        $gyms = $this->repository->findActiveGymsByStudent(new UserId($studentId));

        // Mapear a DTOs
        $items = [];
        foreach ($gyms as $gym) {
            $quotaStatus = $this->calculateQuotaStatus($gym->quota_expires_at);

            $items[] = new StudentGymItemDTO(
                enrollment_id: $gym->enrollment_id,
                enrolled_at: $gym->enrolled_at,
                quota_expires_at: $gym->quota_expires_at,
                quota_status: $quotaStatus,
                gym: [
                    'id' => $gym->gym_id,
                    'name' => $gym->gym_name,
                    'address' => $gym->gym_address,
                    'locality' => $gym->gym_locality,
                    'province' => $gym->gym_province,
                    'country' => $gym->gym_country,
                    'is_personal_training' => (bool) $gym->is_personal_training,
                ],
                trainer: [
                    'id' => $gym->trainer_id,
                    'name' => trim($gym->trainer_name . ' ' . $gym->trainer_last_name),
                    'email' => $gym->trainer_email,
                ]
            );
        }

        // Convertir a array para caché
        $result = array_map(fn($item) => $item->toArray(), $items);

        // Guardar en caché
        $this->cacheService->set($studentId, ['type' => 'gyms'], $result);

        return $result;
    }

    private function calculateQuotaStatus(string $quotaExpiresAt): string
    {
        $expiresAt = new \DateTime($quotaExpiresAt);
        $now = new \DateTime('today');
        $diff = (int) $now->diff($expiresAt)->days;

        if ($expiresAt < $now) {
            return 'expired';
        }

        if ($diff <= 7) {
            return 'expiring_soon';
        }

        return 'active';
    }
}
