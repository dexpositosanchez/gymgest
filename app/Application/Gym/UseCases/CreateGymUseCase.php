<?php

declare(strict_types=1);

namespace App\Application\Gym\UseCases;

use App\Application\Gym\DTOs\CreateGymDTO;
use App\Application\Gym\DTOs\GymResponseDTO;
use App\Domain\Gym\Entities\GymEntity;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\Gym\Services\GymDomainService;
use App\Domain\Gym\ValueObjects\GymAddress;
use App\Domain\Gym\ValueObjects\GymLocality;
use App\Domain\Gym\ValueObjects\GymProvince;
use App\Domain\Gym\ValueObjects\GymCountry;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\Gym\ValueObjects\GymName;
use App\Domain\User\ValueObjects\UserId;

final class CreateGymUseCase
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

    public function execute(CreateGymDTO $dto): GymResponseDTO
    {
        $gym = new GymEntity(
            GymId::generate(),
            new UserId($dto->getTrainerId()),
            new GymName($dto->getName()),
            new GymAddress($dto->getAddress()),
            new GymLocality($dto->getLocality()),
            new GymProvince($dto->getProvince()),
            new GymCountry($dto->getCountry()),
            true
        );

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
            $this->gymDomainService->isAssigned($gym)
        );
    }
}
