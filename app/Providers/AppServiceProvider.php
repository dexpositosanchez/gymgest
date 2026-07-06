<?php

namespace App\Providers;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\Exercise\Repositories\ExerciseRepositoryInterface;
use App\Domain\Exercise\Repositories\MuscleGroupRepositoryInterface;
use App\Domain\Exercise\Repositories\TrainerExercisePreferenceRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\UserEloquentRepository;
use App\Infrastructure\Persistence\Repositories\ExerciseEloquentRepository;
use App\Infrastructure\Persistence\Repositories\MuscleGroupEloquentRepository;
use App\Infrastructure\Persistence\Repositories\TrainerExercisePreferenceEloquentRepository;
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
