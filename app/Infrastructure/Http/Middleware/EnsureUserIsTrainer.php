<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnsureUserIsTrainer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user || $user->user_type !== 'trainer') {
                return response()->json([
                    'error' => 'Esta aplicación es solo para entrenadores'
                ], 403);
            }

            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No autenticado'
            ], 401);
        }
    }
}
