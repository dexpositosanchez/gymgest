<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class StudentOnlyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user || $user->user_type !== 'student') {
                return response()->json([
                    'error' => 'This endpoint is only for students'
                ], 403);
            }

            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }
    }
}
