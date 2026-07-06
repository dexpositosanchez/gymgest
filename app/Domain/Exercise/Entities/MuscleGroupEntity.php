<?php

declare(strict_types=1);

namespace App\Domain\Exercise\Entities;

use App\Domain\Exercise\ValueObjects\MuscleGroupId;
use App\Domain\Exercise\ValueObjects\MuscleGroupName;

class MuscleGroupEntity
{
    /** @var MuscleGroupId */
    private $id;

    /** @var MuscleGroupName */
    private $name;

    /** @var string|null */
    private $description;

    public function __construct(
        MuscleGroupId $id,
        MuscleGroupName $name,
        ?string $description = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }

    public function getId(): MuscleGroupId
    {
        return $this->id;
    }

    public function getName(): MuscleGroupName
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
