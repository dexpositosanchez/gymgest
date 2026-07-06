<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\V1;

use App\Application\Exercise\DTOs\CreateExerciseDTO;
use App\Application\Exercise\DTOs\ExerciseFilterDTO;
use App\Application\Exercise\DTOs\UpdateExerciseDTO;
use App\Application\Exercise\UseCases\CreateCustomExerciseUseCase;
use App\Application\Exercise\UseCases\DeleteCustomExerciseUseCase;
use App\Application\Exercise\UseCases\GetExerciseDetailsUseCase;
use App\Application\Exercise\UseCases\ListExercisesUseCase;
use App\Application\Exercise\UseCases\ListMuscleGroupsUseCase;
use App\Application\Exercise\UseCases\ToggleDefaultExerciseUseCase;
use App\Application\Exercise\UseCases\UpdateCustomExerciseUseCase;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\CreateExerciseRequest;
use App\Infrastructure\Http\Requests\ToggleExerciseRequest;
use App\Infrastructure\Http\Requests\UpdateExerciseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Exercises",
 *     description="Exercise management endpoints"
 * )
 */
class ExerciseController extends Controller
{
    /** @var ListExercisesUseCase */
    private $listExercisesUseCase;

    /** @var GetExerciseDetailsUseCase */
    private $getExerciseDetailsUseCase;

    /** @var CreateCustomExerciseUseCase */
    private $createCustomExerciseUseCase;

    /** @var UpdateCustomExerciseUseCase */
    private $updateCustomExerciseUseCase;

    /** @var DeleteCustomExerciseUseCase */
    private $deleteCustomExerciseUseCase;

    /** @var ToggleDefaultExerciseUseCase */
    private $toggleDefaultExerciseUseCase;

    /** @var ListMuscleGroupsUseCase */
    private $listMuscleGroupsUseCase;

    public function __construct(
        ListExercisesUseCase $listExercisesUseCase,
        GetExerciseDetailsUseCase $getExerciseDetailsUseCase,
        CreateCustomExerciseUseCase $createCustomExerciseUseCase,
        UpdateCustomExerciseUseCase $updateCustomExerciseUseCase,
        DeleteCustomExerciseUseCase $deleteCustomExerciseUseCase,
        ToggleDefaultExerciseUseCase $toggleDefaultExerciseUseCase,
        ListMuscleGroupsUseCase $listMuscleGroupsUseCase
    ) {
        $this->listExercisesUseCase = $listExercisesUseCase;
        $this->getExerciseDetailsUseCase = $getExerciseDetailsUseCase;
        $this->createCustomExerciseUseCase = $createCustomExerciseUseCase;
        $this->updateCustomExerciseUseCase = $updateCustomExerciseUseCase;
        $this->deleteCustomExerciseUseCase = $deleteCustomExerciseUseCase;
        $this->toggleDefaultExerciseUseCase = $toggleDefaultExerciseUseCase;
        $this->listMuscleGroupsUseCase = $listMuscleGroupsUseCase;
        $this->middleware(['jwt.auth', 'trainer.only']);
    }

    /**
     * @OA\Get(
     *     path="/exercises",
     *     summary="List exercises",
     *     tags={"Exercises"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="muscle_group_id",
     *         in="query",
     *         description="Filter by muscle group ID",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by exercise name or description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="include_inactive",
     *         in="query",
     *         description="Include inactive exercises",
     *         required=false,
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Exercise list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Only trainers allowed")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $trainerId = new UserId(auth()->user()->id);

            // Parse include_inactive correctly from query string
            $includeInactive = $request->query('include_inactive');
            $includeInactiveBool = $includeInactive === 'true' || $includeInactive === '1' || $includeInactive === true;

            $filters = new ExerciseFilterDTO(
                $request->query('muscle_group_id'),
                $request->query('search'),
                $includeInactiveBool,
                $request->query('type')
            );

            $exerciseDTOs = $this->listExercisesUseCase->execute($trainerId, $filters);

            // Convert DTOs to array
            $exercisesArray = array_map(function ($dto) {
                return $dto->toArray();
            }, $exerciseDTOs);

            return response()->json([
                'data' => $exercisesArray
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al listar ejercicios'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/exercises/{id}",
     *     summary="Get exercise details",
     *     tags={"Exercises"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Exercise details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Exercise not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Only trainers allowed")
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $trainerId = new UserId(auth()->user()->id);
            $exerciseId = new ExerciseId($id);

            $exercise = $this->getExerciseDetailsUseCase->execute($exerciseId, $trainerId);

            if (!$exercise) {
                return response()->json(['error' => 'Ejercicio no encontrado'], 404);
            }

            return response()->json([
                'data' => $exercise->toArray()
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener ejercicio'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/exercises",
     *     summary="Create custom exercise",
     *     tags={"Exercises"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","description","muscle_group_id"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", minLength=10),
     *             @OA\Property(property="muscle_group_id", type="string", format="uuid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Exercise created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Only trainers allowed")
     * )
     */
    public function store(CreateExerciseRequest $request): JsonResponse
    {
        try {
            $trainerId = new UserId(auth()->user()->id);

            $dto = new CreateExerciseDTO(
                $request->name,
                $request->description,
                $request->muscle_group_id
            );

            $exercise = $this->createCustomExerciseUseCase->execute($dto, $trainerId);

            return response()->json([
                'message' => 'Ejercicio creado exitosamente',
                'data' => [
                    'id' => $exercise->getId()->getValue(),
                    'name' => $exercise->getName()->getValue(),
                    'description' => $exercise->getDescription()->getValue(),
                    'type' => $exercise->getType()->getValue()
                ]
            ], 201);

        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear ejercicio'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/exercises/{id}",
     *     summary="Update custom exercise",
     *     tags={"Exercises"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","description","muscle_group_id"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", minLength=10),
     *             @OA\Property(property="muscle_group_id", type="string", format="uuid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Exercise updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden - Cannot edit this exercise"),
     *     @OA\Response(response=404, description="Exercise not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function update(string $id, UpdateExerciseRequest $request): JsonResponse
    {
        try {
            $trainerId = new UserId(auth()->user()->id);
            $exerciseId = new ExerciseId($id);

            $dto = new UpdateExerciseDTO(
                $request->name,
                $request->description,
                $request->muscle_group_id
            );

            $exercise = $this->updateCustomExerciseUseCase->execute($exerciseId, $dto, $trainerId);

            return response()->json([
                'message' => 'Ejercicio actualizado exitosamente',
                'data' => [
                    'id' => $exercise->getId()->getValue(),
                    'name' => $exercise->getName()->getValue(),
                    'description' => $exercise->getDescription()->getValue(),
                    'type' => $exercise->getType()->getValue()
                ]
            ], 200);

        } catch (\DomainException $e) {
            $statusCode = str_contains($e->getMessage(), 'no encontrado') ? 404 : 403;
            return response()->json(['error' => $e->getMessage()], $statusCode);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar ejercicio'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/exercises/{id}",
     *     summary="Delete custom exercise",
     *     tags={"Exercises"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Exercise deleted successfully"
     *     ),
     *     @OA\Response(response=403, description="Forbidden - Cannot delete this exercise"),
     *     @OA\Response(response=404, description="Exercise not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $trainerId = new UserId(auth()->user()->id);
            $exerciseId = new ExerciseId($id);

            $this->deleteCustomExerciseUseCase->execute($exerciseId, $trainerId);

            return response()->json(null, 204);

        } catch (\DomainException $e) {
            $statusCode = str_contains($e->getMessage(), 'no encontrado') ? 404 : 403;
            return response()->json(['error' => $e->getMessage()], $statusCode);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar ejercicio'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/exercises/{id}/toggle",
     *     summary="Toggle default exercise active status",
     *     tags={"Exercises"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"is_active"},
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Exercise status toggled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden - Only default exercises can be toggled"),
     *     @OA\Response(response=404, description="Exercise not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function toggle(string $id, ToggleExerciseRequest $request): JsonResponse
    {
        try {
            $trainerId = new UserId(auth()->user()->id);
            $exerciseId = new ExerciseId($id);

            $this->toggleDefaultExerciseUseCase->execute(
                $exerciseId,
                $trainerId,
                (bool) $request->is_active
            );

            $status = $request->is_active ? 'activado' : 'desactivado';
            return response()->json([
                'message' => "Ejercicio {$status} exitosamente",
                'data' => [
                    'id' => $id,
                    'is_active' => $request->is_active
                ]
            ], 200);

        } catch (\DomainException $e) {
            $statusCode = str_contains($e->getMessage(), 'no encontrado') ? 404 : 403;
            return response()->json(['error' => $e->getMessage()], $statusCode);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cambiar estado del ejercicio'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/muscle-groups",
     *     summary="List muscle groups",
     *     tags={"Exercises"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Muscle groups list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Only trainers allowed")
     * )
     */
    public function muscleGroups(): JsonResponse
    {
        try {
            $muscleGroups = $this->listMuscleGroupsUseCase->execute();

            $muscleGroupsArray = array_map(function ($muscleGroup) {
                return $muscleGroup->toArray();
            }, $muscleGroups);

            return response()->json([
                'data' => $muscleGroupsArray
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al listar grupos musculares'], 500);
        }
    }
}
