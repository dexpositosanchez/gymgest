<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Observers;

use App\Application\RoutineAssignment\Services\RoutineAssignmentCacheService;
use App\Infrastructure\Persistence\Eloquent\GymStudentEloquentModel;

class GymStudentObserver
{
    private RoutineAssignmentCacheService $cacheService;

    public function __construct(RoutineAssignmentCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the GymStudent "updated" event.
     */
    public function updated(GymStudentEloquentModel $gymStudent): void
    {
        // Invalidate cache when gym_student relationship changes
        $this->cacheService->invalidate($gymStudent->student_id);
    }

    /**
     * Handle the GymStudent "created" event.
     */
    public function created(GymStudentEloquentModel $gymStudent): void
    {
        // Invalidate cache when a new gym_student is created
        $this->cacheService->invalidate($gymStudent->student_id);
    }

    /**
     * Handle the GymStudent "deleted" event.
     */
    public function deleted(GymStudentEloquentModel $gymStudent): void
    {
        // Invalidate cache when gym_student is deleted
        $this->cacheService->invalidate($gymStudent->student_id);
    }
}
