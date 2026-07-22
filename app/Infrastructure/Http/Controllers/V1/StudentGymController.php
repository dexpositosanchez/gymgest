<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\V1;

use App\Application\GymStudent\UseCases\ListStudentGymsUseCase;
use App\Infrastructure\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Student Gyms",
 *     description="Student endpoints for viewing enrolled gyms"
 * )
 */
class StudentGymController extends Controller
{
    private ListStudentGymsUseCase $listStudentGymsUseCase;

    public function __construct(ListStudentGymsUseCase $listStudentGymsUseCase)
    {
        $this->listStudentGymsUseCase = $listStudentGymsUseCase;
    }

    /**
     * @OA\Get(
     *     path="/students/me/gyms",
     *     summary="List all gyms where student is actively enrolled",
     *     tags={"Student Gyms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of gyms with enrollment details",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="enrollment_id", type="string", format="uuid"),
     *                     @OA\Property(property="enrolled_at", type="string", format="date-time"),
     *                     @OA\Property(property="quota_expires_at", type="string", format="date"),
     *                     @OA\Property(property="quota_status", type="string", enum={"active", "expiring_soon", "expired"}),
     *                     @OA\Property(
     *                         property="gym",
     *                         type="object",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="address", type="string"),
     *                         @OA\Property(property="locality", type="string"),
     *                         @OA\Property(property="province", type="string"),
     *                         @OA\Property(property="country", type="string"),
     *                         @OA\Property(property="is_personal_training", type="boolean")
     *                     ),
     *                     @OA\Property(
     *                         property="trainer",
     *                         type="object",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="email", type="string", format="email")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Not a student")
     * )
     */
    public function index(): JsonResponse
    {
        $studentId = auth()->id();
        $gyms = $this->listStudentGymsUseCase->execute($studentId);

        return response()->json(['data' => $gyms]);
    }
}
