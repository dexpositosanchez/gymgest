<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\V1;

use App\Application\RoutineAssignment\UseCases\ListStudentRoutinesUseCase;
use App\Infrastructure\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\ListStudentRoutinesRequest;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Student Routines",
 *     description="Student endpoints for viewing assigned routines"
 * )
 */
class StudentRoutineController extends Controller
{
    private ListStudentRoutinesUseCase $listStudentRoutinesUseCase;

    public function __construct(
        ListStudentRoutinesUseCase $listStudentRoutinesUseCase
    ) {
        $this->listStudentRoutinesUseCase = $listStudentRoutinesUseCase;
    }

    /**
     * @OA\Get(
     *     path="/students/me/routines",
     *     summary="List all assigned routines for authenticated student",
     *     tags={"Student Routines"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page (max 50)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=50, default=10)
     *     ),
     *     @OA\Parameter(
     *         name="gym_id",
     *         in="query",
     *         description="Filter by gym ID",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="trainer_id",
     *         in="query",
     *         description="Filter by trainer ID",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="difficulty",
     *         in="query",
     *         description="Filter by difficulty",
     *         required=false,
     *         @OA\Schema(type="string", enum={"beginner", "intermediate", "advanced"})
     *     ),
     *     @OA\Parameter(
     *         name="from",
     *         in="query",
     *         description="Filter by starts_at from date",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="to",
     *         in="query",
     *         description="Filter by starts_at to date",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of assigned routines with pagination metadata"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Not a student")
     * )
     */
    public function index(ListStudentRoutinesRequest $request): JsonResponse
    {
        $studentId = auth()->user()->id;

        $result = $this->listStudentRoutinesUseCase->execute(
            $studentId,
            $request->getFilters(),
            $request->getPage(),
            $request->getPerPage()
        );

        return response()->json($result->toArray());
    }

    /**
     * @OA\Get(
     *     path="/students/me/routines/current",
     *     summary="List only current routines for authenticated student",
     *     tags={"Student Routines"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page (max 50)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=50, default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of current routines with pagination metadata"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Not a student")
     * )
     */
    public function current(ListStudentRoutinesRequest $request): JsonResponse
    {
        $studentId = auth()->user()->id;

        $filters = array_merge($request->getFilters(), ['is_current' => true]);

        $result = $this->listStudentRoutinesUseCase->execute(
            $studentId,
            $filters,
            $request->getPage(),
            $request->getPerPage()
        );

        return response()->json($result->toArray());
    }
}
