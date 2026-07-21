<?php

namespace App\Providers;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\Exercise\Repositories\ExerciseRepositoryInterface;
use App\Domain\Exercise\Repositories\MuscleGroupRepositoryInterface;
use App\Domain\Exercise\Repositories\TrainerExercisePreferenceRepositoryInterface;
use App\Domain\Routine\Repositories\RoutineRepositoryInterface;
use App\Domain\Routine\Repositories\RoutineDayExerciseRepositoryInterface;
use App\Domain\Routine\Repositories\ExerciseSetRepositoryInterface;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\WorkoutSession\Repositories\WorkoutSessionRepositoryInterface;
use App\Domain\WorkoutSession\Repositories\WorkoutSessionExerciseStatusRepositoryInterface;
use App\Domain\SetExecution\Repositories\SetExecutionRepositoryInterface;
use App\Domain\ExerciseWeightHistory\Repositories\ExerciseWeightHistoryRepositoryInterface;
use App\Domain\Statistics\Repositories\StatisticsRepositoryInterface;
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
use App\Infrastructure\Persistence\Repositories\WorkoutSessionEloquentRepository;
use App\Infrastructure\Persistence\Repositories\WorkoutSessionExerciseStatusEloquentRepository;
use App\Infrastructure\Persistence\Repositories\SetExecutionEloquentRepository;
use App\Infrastructure\Persistence\Repositories\ExerciseWeightHistoryEloquentRepository;
use App\Infrastructure\Persistence\Repositories\StatisticsEloquentRepository;
use App\Infrastructure\Persistence\Repositories\RoutineDayExerciseEloquentRepository;
use App\Infrastructure\Persistence\Repositories\ExerciseSetEloquentRepository;
use App\Infrastructure\Persistence\Eloquent\GymStudentEloquentModel;
use App\Infrastructure\Persistence\Observers\GymStudentObserver;
use App\Domain\Auth\Services\TokenServiceInterface;
use App\Domain\Auth\Services\PasswordResetServiceInterface;
use App\Domain\Mail\Services\EmailServiceInterface;
use App\Domain\RoutineAssignment\Services\RoutineAssignmentCacheServiceInterface;
use App\Domain\ExerciseWeightHistory\Services\WeightHistoryCacheServiceInterface;
use App\Infrastructure\Auth\JWTTokenService;
use App\Infrastructure\Auth\LaravelPasswordResetService;
use App\Infrastructure\Mail\LaravelEmailService;
use App\Infrastructure\Cache\RoutineAssignmentCacheService;
use App\Infrastructure\Cache\WeightHistoryCacheService;
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

        $this->app->bind(
            RoutineDayExerciseRepositoryInterface::class,
            RoutineDayExerciseEloquentRepository::class
        );

        $this->app->bind(
            ExerciseSetRepositoryInterface::class,
            ExerciseSetEloquentRepository::class
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

        // WorkoutSession repositories
        $this->app->bind(
            WorkoutSessionRepositoryInterface::class,
            WorkoutSessionEloquentRepository::class
        );

        $this->app->bind(
            WorkoutSessionExerciseStatusRepositoryInterface::class,
            WorkoutSessionExerciseStatusEloquentRepository::class
        );

        // SetExecution repositories
        $this->app->bind(
            SetExecutionRepositoryInterface::class,
            SetExecutionEloquentRepository::class
        );

        // ExerciseWeightHistory repositories
        $this->app->bind(
            ExerciseWeightHistoryRepositoryInterface::class,
            ExerciseWeightHistoryEloquentRepository::class
        );

        // Statistics repositories
        $this->app->bind(
            StatisticsRepositoryInterface::class,
            StatisticsEloquentRepository::class
        );

        // Infrastructure services
        $this->app->singleton(
            RoutineAssignmentResponseBuilderInterface::class,
            RoutineAssignmentResponseBuilder::class
        );

        // Auth services
        $this->app->bind(
            TokenServiceInterface::class,
            JWTTokenService::class
        );

        $this->app->bind(
            PasswordResetServiceInterface::class,
            LaravelPasswordResetService::class
        );

        // Mail services
        $this->app->bind(
            EmailServiceInterface::class,
            LaravelEmailService::class
        );

        // Cache services
        $this->app->bind(
            RoutineAssignmentCacheServiceInterface::class,
            RoutineAssignmentCacheService::class
        );

        $this->app->bind(
            WeightHistoryCacheServiceInterface::class,
            WeightHistoryCacheService::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register observers
        GymStudentEloquentModel::observe(GymStudentObserver::class);
    }
}
