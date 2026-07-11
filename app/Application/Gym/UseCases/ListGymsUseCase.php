<?php

declare(strict_types=1);

namespace App\Application\Gym\UseCases;

use App\Application\Gym\DTOs\GymResponseDTO;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\Gym\Services\GymDomainService;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

final class ListGymsUseCase
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

    public function execute(UserId $trainerId, bool $includeInactive = false): array
    {
        $gyms = $this->gymRepository->findByTrainerId($trainerId, $includeInactive);

        $response = [];
        foreach ($gyms as $gym) {
            // Skip personal training gyms from the list
            if ($gym->isPersonalTraining()) {
                continue;
            }

            $response[] = new GymResponseDTO(
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

        return $response;
    }
}
