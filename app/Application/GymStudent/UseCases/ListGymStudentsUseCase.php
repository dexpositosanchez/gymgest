<?php

declare(strict_types=1);

namespace App\Application\GymStudent\UseCases;

use App\Application\GymStudent\DTOs\GymStudentResponseDTO;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use App\Domain\GymStudent\Services\GymStudentDomainService;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use InvalidArgumentException;

class ListGymStudentsUseCase
{
    private GymRepositoryInterface $gymRepository;
    private UserRepositoryInterface $userRepository;
    private GymStudentRepositoryInterface $gymStudentRepository;
    private GymStudentDomainService $domainService;

    public function __construct(
        GymRepositoryInterface $gymRepository,
        UserRepositoryInterface $userRepository,
        GymStudentRepositoryInterface $gymStudentRepository,
        GymStudentDomainService $domainService
    ) {
        $this->gymRepository = $gymRepository;
        $this->userRepository = $userRepository;
        $this->gymStudentRepository = $gymStudentRepository;
        $this->domainService = $domainService;
    }

    public function execute(string $gymId, string $trainerId): array
    {
        // Verificar que el gimnasio existe y pertenece al trainer
        $gym = $this->gymRepository->findById(new GymId($gymId));
        if (!$gym) {
            throw new InvalidArgumentException('Gym not found');
        }

        if ($gym->getTrainerId()->getValue() !== $trainerId) {
            throw new InvalidArgumentException('Unauthorized');
        }

        // Obtener todos los estudiantes del gimnasio
        $gymStudents = $this->gymStudentRepository->findByGymId(new GymId($gymId));

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
