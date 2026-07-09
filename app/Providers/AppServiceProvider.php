<?php

namespace App\Providers;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\Exercise\Repositories\ExerciseRepositoryInterface;
use App\Domain\Exercise\Repositories\MuscleGroupRepositoryInterface;
use App\Domain\Exercise\Repositories\TrainerExercisePreferenceRepositoryInterface;
use App\Domain\Routine\Repositories\RoutineRepositoryInterface;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\UserEloquentRepository;
use App\Infrastructure\Persistence\Repositories\ExerciseEloquentRepository;
use App\Application\RoutineAssignment\Services\RoutineAssignmentResponseBuilderInterface;
use App\Infrastructure\Services\RoutineAssignmentResponseBuilder;
use App\Infrastructure\Persistence\Repositories\MuscleGroupEloquentRepository;
use App\Infrastructure\Persistence\Repositories\TrainerExercisePreferenceEloquentRepository;
use App\Infrastructure\Persistence\Repositories\RoutineEloquentRepository;
use App\Infrastructure\Persistence\Repositories\GymEloquentRepository;
use App\Infrastructure\Persistence\Repositories\GymStudentEloquentRepository;
use App\Infrastructure\Persistence\Repositories\RoutineAssignmentEloquentRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // User repositories
        $this->app->bind(
            UserRepositoryInterface::class,
            UserEloquentRepository::class
        );

        // Exercise repositories
        $this->app->bind(
            ExerciseRepositoryInterface::class,
            ExerciseEloquentRepository::class
        );

        $this->app->bind(
            MuscleGroupRepositoryInterface::class,
            MuscleGroupEloquentRepository::class
        );

        $this->app->bind(
            TrainerExercisePreferenceRepositoryInterface::class,
            TrainerExercisePreferenceEloquentRepository::class
        );

        // Routine repositories
        $this->app->bind(
            RoutineRepositoryInterface::class,
            RoutineEloquentRepository::class
        );

        // Gym repositories
        $this->app->bind(
            GymRepositoryInterface::class,
            GymEloquentRepository::class
        );

        // GymStudent repositories
        $this->app->bind(
            GymStudentRepositoryInterface::class,
            GymStudentEloquentRepository::class
        );

        // RoutineAssignment repositories
        $this->app->bind(
            RoutineAssignmentRepositoryInterface::class,
            RoutineAssignmentEloquentRepository::class
        );

        // Infrastructure services
        $this->app->singleton(
            RoutineAssignmentResponseBuilderInterface::class,
            RoutineAssignmentResponseBuilder::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
