<?php

declare(strict_types=1);

namespace App\Domain\Exercise\Repositories;

use App\Domain\Exercise\Entities\MuscleGroupEntity;
use App\Domain\Exercise\ValueObjects\MuscleGroupId;

interface MuscleGroupRepositoryInterface
{
    /**
     * @return MuscleGroupEntity[]
     */
    public function findAll(): array;

    public function findById(MuscleGroupId $id): ?MuscleGroupEntity;
}
