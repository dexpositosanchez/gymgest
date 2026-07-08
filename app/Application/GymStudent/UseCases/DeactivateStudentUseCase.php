<?php

declare(strict_types=1);

namespace App\Application\GymStudent\UseCases;

use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use InvalidArgumentException;

class DeactivateStudentUseCase
{
    private GymRepositoryInterface $gymRepository;
    private GymStudentRepositoryInterface $gymStudentRepository;

    public function __construct(
        GymRepositoryInterface $gymRepository,
        GymStudentRepositoryInterface $gymStudentRepository
    ) {
        $this->gymRepository = $gymRepository;
        $this->gymStudentRepository = $gymStudentRepository;
    }

    public function execute(string $gymId, string $studentId, string $trainerId): void
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

        // Desactivar
        $gymStudent->deactivate();
        $this->gymStudentRepository->save($gymStudent);
    }
}
