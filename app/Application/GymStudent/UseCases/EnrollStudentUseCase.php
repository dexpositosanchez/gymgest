<?php

declare(strict_types=1);

namespace App\Application\GymStudent\UseCases;

use App\Application\Gym\UseCases\GetOrCreatePersonalTrainingGymUseCase;
use App\Application\GymStudent\DTOs\EnrollStudentDTO;
use App\Application\GymStudent\DTOs\GymStudentResponseDTO;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\GymStudent\Entities\GymStudentEntity;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use App\Domain\GymStudent\Services\GymStudentDomainService;
use App\Domain\GymStudent\ValueObjects\GymStudentId;
use App\Domain\GymStudent\ValueObjects\QuotaExpiresAt;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;
use Illuminate\Support\Str;
use InvalidArgumentException;

class EnrollStudentUseCase
{
    private GymRepositoryInterface $gymRepository;
    private UserRepositoryInterface $userRepository;
    private GymStudentRepositoryInterface $gymStudentRepository;
    private GymStudentDomainService $domainService;
    private GetOrCreatePersonalTrainingGymUseCase $getOrCreatePersonalTrainingGymUseCase;

    public function __construct(
        GymRepositoryInterface $gymRepository,
        UserRepositoryInterface $userRepository,
        GymStudentRepositoryInterface $gymStudentRepository,
        GymStudentDomainService $domainService,
        GetOrCreatePersonalTrainingGymUseCase $getOrCreatePersonalTrainingGymUseCase
    ) {
        $this->gymRepository = $gymRepository;
        $this->userRepository = $userRepository;
        $this->gymStudentRepository = $gymStudentRepository;
        $this->domainService = $domainService;
        $this->getOrCreatePersonalTrainingGymUseCase = $getOrCreatePersonalTrainingGymUseCase;
    }

    public function execute(EnrollStudentDTO $dto, string $trainerId): GymStudentResponseDTO
    {
        // Si gym_id es null, obtener o crear gimnasio de entrenamiento personal
        $actualGymId = $dto->gymId;
        if ($actualGymId === null) {
            $personalTrainingGym = $this->getOrCreatePersonalTrainingGymUseCase->execute($trainerId);
            $actualGymId = $personalTrainingGym->toArray()['id'];
        }

        // Verificar que el gimnasio existe y pertenece al trainer
        $gym = $this->gymRepository->findById(new GymId($actualGymId));
        if (!$gym) {
            throw new InvalidArgumentException('Gym not found');
        }

        if ($gym->getTrainerId()->getValue() !== $trainerId) {
            throw new InvalidArgumentException('Unauthorized');
        }

        // Buscar el alumno por email
        $student = $this->userRepository->findByEmail(new Email($dto->email));
        if (!$student) {
            throw new InvalidArgumentException('No existe ningún alumno registrado con ese email');
        }

        // Verificar que es un estudiante
        if (!$this->domainService->canEnroll($gym, $student)) {
            throw new InvalidArgumentException('Este usuario no es un alumno');
        }

        // Verificar si ya existe una matrícula
        $existingEnrollment = $this->gymStudentRepository->findByGymAndStudent(
            new GymId($actualGymId),
            $student->getId()
        );

        if ($existingEnrollment && $existingEnrollment->isActive()) {
            throw new InvalidArgumentException('Este alumno ya está matriculado en este gimnasio');
        }

        // Validar fecha de cuota
        $quotaExpiresAt = QuotaExpiresAt::createForEnrollment($dto->quotaExpiresAt);

        // Si existe pero está inactivo, reactivar
        if ($existingEnrollment && !$existingEnrollment->isActive()) {
            $existingEnrollment->reactivate($quotaExpiresAt);
            $this->gymStudentRepository->save($existingEnrollment);
            $gymStudent = $existingEnrollment;
        } else {
            // Crear nueva matrícula
            $gymStudent = new GymStudentEntity(
                new GymStudentId(Str::uuid()->toString()),
                new GymId($actualGymId),
                $student->getId(),
                $quotaExpiresAt,
                true
            );
            $this->gymStudentRepository->save($gymStudent);
        }

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
