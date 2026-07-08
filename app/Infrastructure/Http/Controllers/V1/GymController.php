<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\V1;

use App\Application\Gym\DTOs\CreateGymDTO;
use App\Application\Gym\DTOs\UpdateGymDTO;
use App\Application\Gym\UseCases\CreateGymUseCase;
use App\Application\Gym\UseCases\DeleteGymUseCase;
use App\Application\Gym\UseCases\GetGymDetailsUseCase;
use App\Application\Gym\UseCases\ListGymsUseCase;
use App\Application\Gym\UseCases\ToggleGymUseCase;
use App\Application\Gym\UseCases\UpdateGymUseCase;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\CreateGymRequest;
use App\Infrastructure\Http\Requests\UpdateGymRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Gyms",
 *     description="Gym management endpoints"
 * )
 */
class GymController extends Controller
{
    private $listGymsUseCase;
    private $getGymDetailsUseCase;
    private $createGymUseCase;
    private $updateGymUseCase;
    private $deleteGymUseCase;
    private $toggleGymUseCase;

    public function __construct(
        ListGymsUseCase $listGymsUseCase,
        GetGymDetailsUseCase $getGymDetailsUseCase,
        CreateGymUseCase $createGymUseCase,
        UpdateGymUseCase $updateGymUseCase,
        DeleteGymUseCase $deleteGymUseCase,
        ToggleGymUseCase $toggleGymUseCase
    ) {
        $this->listGymsUseCase = $listGymsUseCase;
        $this->getGymDetailsUseCase = $getGymDetailsUseCase;
        $this->createGymUseCase = $createGymUseCase;
        $this->updateGymUseCase = $updateGymUseCase;
        $this->deleteGymUseCase = $deleteGymUseCase;
        $this->toggleGymUseCase = $toggleGymUseCase;
        $this->middleware(['jwt.auth', 'trainer.only']);
    }

    /**
     * @OA\Get(
     *     path="/gyms",
     *     summary="List gyms",
     *     tags={"Gyms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="include_inactive",
     *         in="query",
     *         description="Include inactive gyms",
     *         required=false,
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gym list",
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

            $includeInactive = $request->query('include_inactive');
            $includeInactiveBool = $includeInactive === 'true' || $includeInactive === '1' || $includeInactive === true;

            $gymDTOs = $this->listGymsUseCase->execute($trainerId, $includeInactiveBool);

            $gymsArray = array_map(function ($dto) {
                return $dto->toArray();
            }, $gymDTOs);

            return response()->json([
                'data' => $gymsArray
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al listar gimnasios'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/gyms/{id}",
     *     summary="Get gym details",
     *     tags={"Gyms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gym details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Gym not found"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $trainerId = new UserId(auth()->user()->id);
            $gymId = new GymId($id);

            $gym = $this->getGymDetailsUseCase->execute($gymId, $trainerId);

            return response()->json([
                'data' => $gym->toArray()
            ], 200);

        } catch (\InvalidArgumentException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return response()->json(['error' => 'Gimnasio no encontrado'], 404);
            }
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener gimnasio'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/gyms",
     *     summary="Create gym",
     *     tags={"Gyms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","address","locality","province","country"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="address", type="string", maxLength=255),
     *             @OA\Property(property="locality", type="string", maxLength=100),
     *             @OA\Property(property="province", type="string", maxLength=100),
     *             @OA\Property(property="country", type="string", maxLength=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Gym created",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Only trainers allowed")
     * )
     */
    public function store(CreateGymRequest $request): JsonResponse
    {
        try {
            $trainerId = auth()->user()->id;

            $dto = new CreateGymDTO(
                $trainerId,
                $request->input('name'),
                $request->input('address'),
                $request->input('locality'),
                $request->input('province'),
                $request->input('country')
            );

            $gym = $this->createGymUseCase->execute($dto);

            return response()->json([
                'data' => $gym->toArray()
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear gimnasio'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/gyms/{id}",
     *     summary="Update gym",
     *     tags={"Gyms"},
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
     *             required={"name","address","locality","province","country"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="address", type="string", maxLength=255),
     *             @OA\Property(property="locality", type="string", maxLength=100),
     *             @OA\Property(property="province", type="string", maxLength=100),
     *             @OA\Property(property="country", type="string", maxLength=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gym updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Gym not found"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function update(UpdateGymRequest $request, string $id): JsonResponse
    {
        try {
            $trainerId = auth()->user()->id;

            $dto = new UpdateGymDTO(
                $id,
                $trainerId,
                $request->input('name'),
                $request->input('address'),
                $request->input('locality'),
                $request->input('province'),
                $request->input('country')
            );

            $gym = $this->updateGymUseCase->execute($dto);

            return response()->json([
                'data' => $gym->toArray()
            ], 200);

        } catch (\InvalidArgumentException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return response()->json(['error' => 'Gimnasio no encontrado'], 404);
            }
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar gimnasio'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/gyms/{id}",
     *     summary="Delete gym",
     *     tags={"Gyms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Gym deleted"
     *     ),
     *     @OA\Response(response=404, description="Gym not found"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $trainerId = new UserId(auth()->user()->id);
            $gymId = new GymId($id);

            $this->deleteGymUseCase->execute($gymId, $trainerId);

            return response()->json(null, 204);

        } catch (\InvalidArgumentException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return response()->json(['error' => 'Gimnasio no encontrado'], 404);
            }
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar gimnasio'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/gyms/{id}/toggle",
     *     summary="Toggle gym active status",
     *     tags={"Gyms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gym status toggled",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Gym not found"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function toggle(string $id): JsonResponse
    {
        try {
            $trainerId = new UserId(auth()->user()->id);
            $gymId = new GymId($id);

            $gym = $this->toggleGymUseCase->execute($gymId, $trainerId);

            return response()->json([
                'data' => $gym->toArray()
            ], 200);

        } catch (\InvalidArgumentException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return response()->json(['error' => 'Gimnasio no encontrado'], 404);
            }
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cambiar estado del gimnasio'], 500);
        }
    }
}
