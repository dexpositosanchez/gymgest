<?php

declare(strict_types=1);

namespace App\Application\Gym\UseCases;

use App\Application\Gym\DTOs\GymResponseDTO;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\Gym\Services\GymDomainService;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;

final class GetOrCreatePersonalTrainingGymUseCase
{
    private $gymRepository;
    private $gymDomainService;
    private $gymStudentRepository;

    public function __construct(
        GymRepositoryInterface $gymRepository,
        GymDomainService $gymDomainService,
        GymStudentRepositoryInterface $gymStudentRepository
    ) {
        $this->gymRepository = $gymRepository;
        $this->gymDomainService = $gymDomainService;
        $this->gymStudentRepository = $gymStudentRepository;
    }

    /**
     * Get or create personal training gym (for internal use from EnrollStudentUseCase)
     */
    public function execute(string $trainerId): GymResponseDTO
    {
        $trainerIdVO = new UserId($trainerId);

        // Try to find existing personal training gym for this trainer
        $gym = $this->gymRepository->findPersonalTrainingGymByTrainer($trainerIdVO);

        // If doesn't exist, create it
        if ($gym === null) {
            $gym = $this->gymDomainService->createPersonalTrainingGym(
                GymId::generate(),
                $trainerIdVO
            );
            $this->gymRepository->save($gym);
        }

        return new GymResponseDTO(
            $gym->getId()->getValue(),
            $gym->getTrainerId()->getValue(),
            $gym->getName()->getValue(),
            $gym->getAddress()->getValue(),
            $gym->getLocality()->getValue(),
            $gym->getProvince()->getValue(),
            $gym->getCountry()->getValue(),
            $gym->isActive(),
            $this->gymDomainService->isAssigned($gym),
            $this->gymStudentRepository->countActiveByGym($gym->getId()),
            $gym->isPersonalTraining()
        );
    }
}
