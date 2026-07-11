<?php

declare(strict_types=1);

namespace App\Application\Gym\UseCases;

use App\Application\Gym\DTOs\GymResponseDTO;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\Gym\Services\GymDomainService;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;

final class GetPersonalTrainingGymUseCase
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
     * Get personal training gym (read-only, returns null if doesn't exist)
     */
    public function execute(string $trainerId): ?GymResponseDTO
    {
        $trainerIdVO = new UserId($trainerId);

        $gym = $this->gymRepository->findPersonalTrainingGymByTrainer($trainerIdVO);

        if ($gym === null) {
            return null;
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
