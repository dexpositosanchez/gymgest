<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\V1;

use App\Application\Routine\DTOs\CreateRoutineDTO;
use App\Application\Routine\DTOs\UpdateRoutineDTO;
use App\Application\Routine\UseCases\CreateRoutineUseCase;
use App\Application\Routine\UseCases\DeleteRoutineUseCase;
use App\Application\Routine\UseCases\GetRoutineDetailsUseCase;
use App\Application\Routine\UseCases\ListRoutinesUseCase;
use App\Application\Routine\UseCases\UpdateRoutineUseCase;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\CreateRoutineRequest;
use App\Infrastructure\Http\Requests\UpdateRoutineRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Routines",
 *     description="Routine management endpoints"
 * )
 */
class RoutineController extends Controller
{
    /** @var CreateRoutineUseCase */
    private $createRoutineUseCase;

    /** @var ListRoutinesUseCase */
    private $listRoutinesUseCase;

    /** @var GetRoutineDetailsUseCase */
    private $getRoutineDetailsUseCase;

    /** @var UpdateRoutineUseCase */
    private $updateRoutineUseCase;

    /** @var DeleteRoutineUseCase */
    private $deleteRoutineUseCase;

    public function __construct(
        CreateRoutineUseCase $createRoutineUseCase,
        ListRoutinesUseCase $listRoutinesUseCase,
        GetRoutineDetailsUseCase $getRoutineDetailsUseCase,
        UpdateRoutineUseCase $updateRoutineUseCase,
        DeleteRoutineUseCase $deleteRoutineUseCase
    ) {
        $this->createRoutineUseCase = $createRoutineUseCase;
        $this->listRoutinesUseCase = $listRoutinesUseCase;
        $this->getRoutineDetailsUseCase = $getRoutineDetailsUseCase;
        $this->updateRoutineUseCase = $updateRoutineUseCase;
        $this->deleteRoutineUseCase = $deleteRoutineUseCase;
        $this->middleware(['jwt.auth', 'trainer.only']);
    }

    /**
     * @OA\Get(
     *     path="/routines",
     *     summary="List routines",
     *     tags={"Routines"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="difficulty",
     *         in="query",
     *         description="Filter by difficulty",
     *         required=false,
     *         @OA\Schema(type="string", enum={"beginner", "intermediate", "advanced"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by routine name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Routine list",
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

            $filters = [
                'difficulty' => $request->query('difficulty'),
                'search' => $request->query('search'),
            ];

            $routineDTOs = $this->listRoutinesUseCase->execute($trainerId, $filters);

            $routinesArray = array_map(function ($dto) {
                return $dto->toArray();
            }, $routineDTOs);

            return response()->json([
                'data' => $routinesArray
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al listar rutinas'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/routines/{id}",
     *     summary="Get routine details",
     *     tags={"Routines"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Routine details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Routine not found"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $trainerId = new UserId(auth()->user()->id);
            $routineId = new RoutineId($id);

            $routine = $this->getRoutineDetailsUseCase->execute($routineId, $trainerId);

            if (!$routine) {
                return response()->json(['error' => 'Rutina no encontrada'], 404);
            }

            return response()->json([
                'data' => $routine->toArray()
            ], 200);

        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener rutina'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/routines",
     *     summary="Create routine",
     *     tags={"Routines"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","difficulty","days"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="difficulty", type="string", enum={"beginner","intermediate","advanced"}),
     *             @OA\Property(property="days", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Routine created successfully",
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
    public function store(CreateRoutineRequest $request): JsonResponse
    {
        try {
            $trainerId = new UserId(auth()->user()->id);

            $dto = new CreateRoutineDTO(
                $request->name,
                $request->description,
                $request->difficulty,
                $request->days
            );

            $routine = $this->createRoutineUseCase->execute($dto, $trainerId);

            return response()->json([
                'message' => 'Rutina creada exitosamente',
                'data' => [
                    'id' => $routine->getId()->getValue(),
                    'name' => $routine->getName()->getValue(),
                    'description' => $routine->getDescription() ? $routine->getDescription()->getValue() : null,
                    'difficulty' => $routine->getDifficulty()->getValue(),
                ]
            ], 201);

        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear rutina'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/routines/{id}",
     *     summary="Update routine",
     *     tags={"Routines"},
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
     *             required={"name","difficulty","days"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="difficulty", type="string", enum={"beginner","intermediate","advanced"}),
     *             @OA\Property(property="days", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Routine updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Routine not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function update(string $id, UpdateRoutineRequest $request): JsonResponse
    {
        try {
            $trainerId = new UserId(auth()->user()->id);
            $routineId = new RoutineId($id);

            $dto = new UpdateRoutineDTO(
                $request->name,
                $request->description,
                $request->difficulty,
                $request->days
            );

            $routine = $this->updateRoutineUseCase->execute($routineId, $dto, $trainerId);

            return response()->json([
                'message' => 'Rutina actualizada exitosamente',
                'data' => [
                    'id' => $routine->getId()->getValue(),
                    'name' => $routine->getName()->getValue(),
                    'description' => $routine->getDescription() ? $routine->getDescription()->getValue() : null,
                    'difficulty' => $routine->getDifficulty()->getValue(),
                ]
            ], 200);

        } catch (\DomainException $e) {
            // Handle different domain exceptions with appropriate status codes
            if (str_contains($e->getMessage(), 'no encontrada')) {
                return response()->json(['error' => $e->getMessage()], 404);
            } elseif (str_contains($e->getMessage(), 'Cannot update routine with active assignments')) {
                return response()->json(['error' => $e->getMessage()], 400);
            } else {
                return response()->json(['error' => $e->getMessage()], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar rutina'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/routines/{id}",
     *     summary="Delete routine",
     *     tags={"Routines"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Routine deleted successfully"
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Routine not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $trainerId = new UserId(auth()->user()->id);
            $routineId = new RoutineId($id);

            $this->deleteRoutineUseCase->execute($routineId, $trainerId);

            return response()->json(null, 204);

        } catch (\DomainException $e) {
            // Handle different domain exceptions with appropriate status codes
            if (str_contains($e->getMessage(), 'no encontrada')) {
                return response()->json(['error' => $e->getMessage()], 404);
            } elseif (str_contains($e->getMessage(), 'No se puede eliminar esta rutina porque está asignada a un alumno')) {
                return response()->json(['error' => $e->getMessage()], 400);
            } else {
                return response()->json(['error' => $e->getMessage()], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar rutina'], 500);
        }
    }
}
