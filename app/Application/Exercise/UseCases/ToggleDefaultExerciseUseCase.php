<?php

declare(strict_types=1);

namespace App\Application\Exercise\UseCases;

use App\Domain\Exercise\Entities\TrainerExercisePreferenceEntity;
use App\Domain\Exercise\Repositories\ExerciseRepositoryInterface;
use App\Domain\Exercise\Repositories\TrainerExercisePreferenceRepositoryInterface;
use App\Domain\Exercise\Services\ExerciseDomainService;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\Exercise\ValueObjects\PreferenceId;
use App\Domain\User\ValueObjects\UserId;
use Ramsey\Uuid\Uuid;

class ToggleDefaultExerciseUseCase
{
    /** @var ExerciseRepositoryInterface */
    private $exerciseRepository;

    /** @var TrainerExercisePreferenceRepositoryInterface */
    private $preferenceRepository;

    /** @var ExerciseDomainService */
    private $exerciseDomainService;

    public function __construct(
        ExerciseRepositoryInterface $exerciseRepository,
        TrainerExercisePreferenceRepositoryInterface $preferenceRepository,
        ExerciseDomainService $exerciseDomainService
    ) {
        $this->exerciseRepository = $exerciseRepository;
        $this->preferenceRepository = $preferenceRepository;
        $this->exerciseDomainService = $exerciseDomainService;
    }

    public function execute(ExerciseId $id, UserId $trainerId, bool $isActive): void
    {
        $exercise = $this->exerciseRepository->findById($id);

        if (!$exercise) {
            throw new \DomainException('Ejercicio no encontrado');
        }

        if (!$this->exerciseDomainService->canTrainerToggleExercise($exercise)) {
            throw new \DomainException('Solo se pueden activar/desactivar ejercicios por defecto');
        }

        // Buscar preferencia existente
        $preference = $this->preferenceRepository->findByTrainerAndExercise($trainerId, $id);

        if (!$preference) {
            // Crear nueva preferencia
            $preference = new TrainerExercisePreferenceEntity(
                new PreferenceId(Uuid::uuid4()->toString()),
                $trainerId,
                $id,
                $isActive
            );
        } else {
            // Actualizar preferencia existente
            if ($isActive) {
                $preference->activate();
            } else {
                $preference->deactivate();
            }
        }

        $this->preferenceRepository->save($preference);
    }
}
