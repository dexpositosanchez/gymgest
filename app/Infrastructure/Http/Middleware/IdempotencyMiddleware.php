<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class IdempotencyMiddleware
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
        // Skip idempotency for read-only methods
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        // If no Idempotency-Key header, continue without idempotency
        $idempotencyKey = $request->header('Idempotency-Key');
        if (!$idempotencyKey) {
            return $next($request);
        }

        // Validate UUID format
        if (!$this->isValidUuidV4($idempotencyKey)) {
            return response()->json([
                'error' => 'Invalid Idempotency-Key format (must be UUIDv4)'
            ], 400);
        }

        $redisKey = "idempotency:{$idempotencyKey}";

        try {
            // Check if we have a cached response
            $cachedResponse = Redis::get($redisKey);

            if ($cachedResponse !== null) {
                // Return cached response
                $data = json_decode($cachedResponse, true);

                return response()
                    ->json(json_decode($data['body'], true), $data['status'])
                    ->withHeaders(array_merge(
                        $data['headers'] ?? [],
                        ['X-Idempotent-Replayed' => 'true']
                    ));
            }

            // Process request normally
            $response = $next($request);

            // Cache the response if successful (2xx or 4xx)
            if ($response->getStatusCode() < 500) {
                $ttlHours = (int) env('IDEMPOTENCY_TTL_HOURS', 24);
                $ttlSeconds = $ttlHours * 3600;

                $cacheData = [
                    'status' => $response->getStatusCode(),
                    'headers' => [
                        'Content-Type' => $response->headers->get('Content-Type'),
                    ],
                    'body' => $response->getContent(),
                ];

                Redis::setex($redisKey, $ttlSeconds, json_encode($cacheData));
            }

            return $response;

        } catch (\Exception $e) {
            // Redis is down or error occurred - degrade gracefully
            Log::warning('Idempotency middleware: Redis unavailable', [
                'error' => $e->getMessage(),
                'key' => $idempotencyKey
            ]);

            // Continue without idempotency
            return $next($request);
        }
    }

    /**
     * Validate UUID v4 format.
     *
     * @param string $uuid
     * @return bool
     */
    private function isValidUuidV4(string $uuid): bool
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        return preg_match($pattern, $uuid) === 1;
    }
}
