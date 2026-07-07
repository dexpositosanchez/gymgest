<?php

use App\Infrastructure\Http\Controllers\V1\AuthController;
use App\Infrastructure\Http\Controllers\V1\ExerciseController;
use App\Infrastructure\Http\Controllers\V1\RoutineController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verify'])->name('verification.verify');
    Route::post('email/resend', [AuthController::class, 'resend']);
    Route::post('password/email', [AuthController::class, 'requestPasswordReset']);
    Route::post('password/reset', [AuthController::class, 'resetPassword']);
});

// Exercise routes - require authentication and trainer role
Route::middleware(['jwt.auth', 'trainer.only'])->group(function () {
    Route::get('exercises', [ExerciseController::class, 'index']);
    Route::get('exercises/{id}', [ExerciseController::class, 'show']);
    Route::post('exercises', [ExerciseController::class, 'store']);
    Route::put('exercises/{id}', [ExerciseController::class, 'update']);
    Route::delete('exercises/{id}', [ExerciseController::class, 'destroy']);
    Route::put('exercises/{id}/toggle', [ExerciseController::class, 'toggle']);
    Route::get('muscle-groups', [ExerciseController::class, 'muscleGroups']);
});

// Routine routes - require authentication and trainer role
Route::middleware(['jwt.auth', 'trainer.only'])->group(function () {
    Route::get('routines', [RoutineController::class, 'index']);
    Route::get('routines/{id}', [RoutineController::class, 'show']);
    Route::post('routines', [RoutineController::class, 'store']);
    Route::put('routines/{id}', [RoutineController::class, 'update']);
    Route::delete('routines/{id}', [RoutineController::class, 'destroy']);
});