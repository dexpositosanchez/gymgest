<?php

declare(strict_types=1);

namespace App\Application\GymStudent\UseCases;

use App\Application\GymStudent\DTOs\GymStudentResponseDTO;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use App\Domain\GymStudent\Services\GymStudentDomainService;
use App\Domain\GymStudent\ValueObjects\QuotaExpiresAt;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use InvalidArgumentException;

class ReactivateStudentUseCase
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

    public function execute(string $gymId, string $studentId, string $quotaExpiresAt, string $trainerId): GymStudentResponseDTO
    {
        // Verificar que el gimnasio existe y pertenece al trainer
        $gym = $this->gymRepository->findById(new GymId($gymId));
        if (!$gym) {
            throw new InvalidArgumentException('Gym not found');
        }

        if ($gym->getTrainerId()->getValue() !== $trainerId) {
            throw new InvalidArgumentException('Unauthorized');
        }

        // Buscar la matrícula
        $gymStudent = $this->gymStudentRepository->findByGymAndStudent(
            new GymId($gymId),
            new UserId($studentId)
        );

        if (!$gymStudent) {
            throw new InvalidArgumentException('Student not enrolled in this gym');
        }

        // Reactivar con nueva fecha de cuota
        $newQuota = QuotaExpiresAt::createForEnrollment($quotaExpiresAt);
        $gymStudent->reactivate($newQuota);
        $this->gymStudentRepository->save($gymStudent);

        // Obtener datos del estudiante
        $student = $this->userRepository->findById(new UserId($studentId));
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
    }
}
