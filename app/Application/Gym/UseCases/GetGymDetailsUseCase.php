<?php

declare(strict_types=1);

namespace App\Application\Gym\UseCases;

use App\Application\Gym\DTOs\GymResponseDTO;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\Gym\Services\GymDomainService;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\User\ValueObjects\UserId;
use InvalidArgumentException;

final class GetGymDetailsUseCase
{
    private $gymRepository;
    private $gymDomainService;

    public function __construct(
        GymRepositoryInterface $gymRepository,
        GymDomainService $gymDomainService
    ) {
        $this->gymRepository = $gymRepository;
        $this->gymDomainService = $gymDomainService;
    }

    public function execute(GymId $gymId, UserId $trainerId): GymResponseDTO
    {
        $gym = $this->gymRepository->findById($gymId);

        if (!$gym) {
            throw new InvalidArgumentException('Gym not found');
        }

        if (!$this->gymDomainService->canTrainerModify($gym, $trainerId)) {
            throw new InvalidArgumentException('You do not have permission to view this gym');
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
            $this->gymDomainService->isAssigned($gym)
        );
    }
}
