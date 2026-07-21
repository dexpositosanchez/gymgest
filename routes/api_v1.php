<?php

use App\Infrastructure\Http\Controllers\V1\AuthController;
use App\Infrastructure\Http\Controllers\V1\ExerciseController;
use App\Infrastructure\Http\Controllers\V1\GymController;
use App\Infrastructure\Http\Controllers\V1\GymStudentController;
use App\Infrastructure\Http\Controllers\V1\RoutineController;
use App\Infrastructure\Http\Controllers\V1\RoutineAssignmentController;
use App\Infrastructure\Http\Controllers\V1\StudentRoutineController;
use App\Infrastructure\Http\Controllers\V1\StudentGymController;
use App\Infrastructure\Http\Controllers\V1\WorkoutSessionController;
use App\Infrastructure\Http\Controllers\V1\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verify'])->name('verification.verify');
    Route::post('email/resend', [AuthController::class, 'resend']);
    Route::post('password/email', [AuthController::class, 'requestPasswordReset'])->middleware('throttle:6,1');
    Route::post('password/reset', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1');
});

// Student routes - MUST be before trainer routes to avoid route conflicts
// (students/me/... must match before students/{studentId}/...)
Route::middleware(['jwt.auth', 'student.only'])->group(function () {
    Route::get('students/me/routines', [StudentRoutineController::class, 'index']);
    Route::get('students/me/routines/current', [StudentRoutineController::class, 'current']);

    // Student Gyms (TASK_029)
    Route::get('students/me/gyms', [StudentGymController::class, 'index']);

    // Workout Session routes (TASK_030)
    Route::post('students/me/workout-sessions', [WorkoutSessionController::class, 'start']);
    Route::get('students/me/workout-sessions/active', [WorkoutSessionController::class, 'getActive']);
    Route::get('students/me/workout-sessions', [WorkoutSessionController::class, 'history']);
    Route::put('students/me/workout-sessions/{sessionId}/finish', [WorkoutSessionController::class, 'finish']);
    Route::get('students/me/workout-sessions/{sessionId}/exercises/{exerciseId}/sets', [WorkoutSessionController::class, 'getSets']);
    Route::post('students/me/workout-sessions/{sessionId}/exercises/{exerciseId}/sets', [WorkoutSessionController::class, 'executeSet']);
    Route::put('students/me/workout-sessions/{sessionId}/exercises/{exerciseId}/mark-complete', [WorkoutSessionController::class, 'markExerciseComplete']);

    // Statistics routes - Student endpoints (TASK_031 + TASK_032)
    Route::get('students/me/statistics/routines', [StatisticsController::class, 'myRoutineStats']);
    Route::get('students/me/statistics/exercise-weight-history', [StatisticsController::class, 'myExerciseWeightHistory']);
    Route::get('students/me/statistics/exercises-executed', [StatisticsController::class, 'myExecutedExercises']);
    Route::get('students/me/gyms/{gymId}/statistics/active-students', [StatisticsController::class, 'myGymActiveStudents']);
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

// Gym routes - require authentication and trainer role
Route::middleware(['jwt.auth', 'trainer.only'])->group(function () {
    Route::get('gyms', [GymController::class, 'index']);
    Route::get('gyms/{id}', [GymController::class, 'show']);
    Route::post('gyms', [GymController::class, 'store']);
    Route::put('gyms/{id}', [GymController::class, 'update']);
    Route::delete('gyms/{id}', [GymController::class, 'destroy']);
    Route::put('gyms/{id}/toggle', [GymController::class, 'toggle']);
    Route::get('personal-training', [GymController::class, 'getPersonalTraining']);
    Route::post('personal-training/students', [GymStudentController::class, 'personalTrainingEnroll']);

    // Gym Students routes
    Route::get('gyms/{gymId}/students', [GymStudentController::class, 'index']);
    Route::post('gyms/{gymId}/students', [GymStudentController::class, 'store']);
    Route::put('gyms/{gymId}/students/{studentId}', [GymStudentController::class, 'update']);
    Route::delete('gyms/{gymId}/students/{studentId}', [GymStudentController::class, 'destroy']);
    Route::put('gyms/{gymId}/students/{studentId}/deactivate', [GymStudentController::class, 'deactivate']);
    Route::put('gyms/{gymId}/students/{studentId}/reactivate', [GymStudentController::class, 'reactivate']);

    // Routine Assignment routes
    Route::get('gyms/{gymId}/students/{studentId}/routines', [RoutineAssignmentController::class, 'index']);
    Route::post('gyms/{gymId}/students/{studentId}/routines', [RoutineAssignmentController::class, 'store']);
    Route::put('gyms/{gymId}/students/{studentId}/routines/{assignmentId}', [RoutineAssignmentController::class, 'update']);
    Route::delete('gyms/{gymId}/students/{studentId}/routines/{assignmentId}', [RoutineAssignmentController::class, 'destroy']);
    Route::put('gyms/{gymId}/students/{studentId}/routines/{assignmentId}/set-current', [RoutineAssignmentController::class, 'setCurrent']);

    // List all students from all trainer's gyms
    Route::get('students', [GymStudentController::class, 'listAll']);

    // Statistics routes - Trainer endpoints (TASK_031 + TASK_032)
    Route::get('students/{studentId}/statistics/routines', [StatisticsController::class, 'studentRoutineStats']);
    Route::get('students/{studentId}/statistics/exercise-weight-history', [StatisticsController::class, 'studentExerciseWeightHistory']);
    Route::get('students/{studentId}/statistics/exercises-executed', [StatisticsController::class, 'studentExecutedExercises']);
    Route::get('gyms/{gymId}/statistics/active-students', [StatisticsController::class, 'gymActiveStudents']);
});