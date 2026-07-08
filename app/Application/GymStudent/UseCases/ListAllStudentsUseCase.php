<?php

declare(strict_types=1);

namespace App\Application\GymStudent\UseCases;

use App\Application\GymStudent\DTOs\GymStudentResponseDTO;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use App\Domain\GymStudent\Services\GymStudentDomainService;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

/**
 * ListAllStudentsUseCase: obtiene primero todos los gym_ids del entrenador autenticado,
 * luego lista los gym_students de esos gimnasios.
 * NUNCA devuelve alumnos de gimnasios de otros entrenadores.
 */
class ListAllStudentsUseCase
{
    private UserRepositoryInterface $userRepository;
    private GymStudentRepositoryInterface $gymStudentRepository;
    private GymStudentDomainService $domainService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        GymStudentRepositoryInterface $gymStudentRepository,
        GymStudentDomainService $domainService
    ) {
        $this->userRepository = $userRepository;
        $this->gymStudentRepository = $gymStudentRepository;
        $this->domainService = $domainService;
    }

    public function execute(string $trainerId): array
    {
        // Obtener todos los estudiantes de todos los gimnasios del trainer
        $gymStudents = $this->gymStudentRepository->findByTrainerId(new UserId($trainerId));

        // Mapear a DTOs
        return array_map(function ($gymStudent) {
            $student = $this->userRepository->findById($gymStudent->getStudentId());
            $fullName = $student->getName()->getValue() . ' ' . $student->getLastName()->getValue();

            return new GymStudentResponseDTO(
                $gymStudent->getId()->getValue(),
                $gymStudent->getGymId()->getValue(),
                $gymStudent->getStudentId()->getValue(),
                $fullName,
                $student->getEmail()->getValue(),
                $gymStudent->getQuotaExpiresAt()->getValue(),
                $gymStudent->isActive(),
                $this->domainService->getQuotaStatus($gymStudent)
            );
        }, $gymStudents);
    }
}
