<?php

declare(strict_types=1);

namespace App\Application\Gym\UseCases;

use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\Gym\Services\GymDomainService;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\User\ValueObjects\UserId;
use InvalidArgumentException;

final class DeleteGymUseCase
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

    public function execute(GymId $gymId, UserId $trainerId): void
    {
        $gym = $this->gymRepository->findById($gymId);

        if (!$gym) {
            throw new InvalidArgumentException('Gym not found');
        }

        if (!$this->gymDomainService->canTrainerModify($gym, $trainerId)) {
            throw new InvalidArgumentException('You do not have permission to delete this gym');
        }

        if ($this->gymDomainService->isAssigned($gym)) {
            throw new InvalidArgumentException('Cannot delete gym with assigned students');
        }

        $this->gymRepository->delete($gymId);
    }
}
