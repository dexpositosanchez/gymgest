<?php

declare(strict_types=1);

namespace App\Application\Gym\UseCases;

use App\Application\Gym\DTOs\GymResponseDTO;
use App\Application\Gym\DTOs\UpdateGymDTO;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\Gym\Services\GymDomainService;
use App\Domain\Gym\ValueObjects\GymAddress;
use App\Domain\Gym\ValueObjects\GymLocality;
use App\Domain\Gym\ValueObjects\GymProvince;
use App\Domain\Gym\ValueObjects\GymCountry;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\Gym\ValueObjects\GymName;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use InvalidArgumentException;

final class UpdateGymUseCase
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

    public function execute(UpdateGymDTO $dto): GymResponseDTO
    {
        $gym = $this->gymRepository->findById(new GymId($dto->getGymId()));

        if (!$gym) {
            throw new InvalidArgumentException('Gym not found');
        }

        if (!$this->gymDomainService->canTrainerModify($gym, new UserId($dto->getTrainerId()))) {
            throw new InvalidArgumentException('You do not have permission to update this gym');
        }

        $gym->updateName(new GymName($dto->getName()));
        $gym->updateAddress(new GymAddress($dto->getAddress()));
        $gym->updateLocality(new GymLocality($dto->getLocality()));
        $gym->updateProvince(new GymProvince($dto->getProvince()));
        $gym->updateCountry(new GymCountry($dto->getCountry()));

        $this->gymRepository->save($gym);

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
            $this->gymStudentRepository->countActiveByGym($gym->getId())
        );
    }
}
