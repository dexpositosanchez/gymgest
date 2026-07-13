<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RoutineAssignmentCacheService
{
    private const CACHE_PREFIX = 'student:routines:';
    private const DEFAULT_TTL = 300; // 5 minutes

    /**
     * Generate cache key for student routine list
     */
    public function getCacheKey(string $studentId, array $params): string
    {
        ksort($params); // Sort params for consistent key
        $hash = md5(json_encode($params));
        return self::CACHE_PREFIX . $studentId . ':' . $hash;
    }

    /**
     * Get cached data
     */
    public function get(string $studentId, array $params): ?array
    {
        try {
            $key = $this->getCacheKey($studentId, $params);
            return Cache::get($key);
        } catch (\Exception $e) {
            Log::warning('Cache get failed', [
                'error' => $e->getMessage(),
                'student_id' => $studentId
            ]);
            return null;
        }
    }

    /**
     * Store data in cache
     */
    public function set(string $studentId, array $params, array $data, int $ttl = self::DEFAULT_TTL): void
    {
        try {
            $key = $this->getCacheKey($studentId, $params);
            Cache::put($key, $data, $ttl);
        } catch (\Exception $e) {
            Log::warning('Cache set failed', [
                'error' => $e->getMessage(),
                'student_id' => $studentId
            ]);
            // Fail silently - graceful degradation
        }
    }

    /**
     * Invalidate all cache entries for a student
     */
    public function invalidate(string $studentId): void
    {
        try {
            $cacheDriver = config('cache.default');
            $pattern = self::CACHE_PREFIX . $studentId . ':*';

            // Different strategies based on cache driver
            if (in_array($cacheDriver, ['array', 'file'])) {
                // For array/file cache, flush everything (tests or simple setups)
                Cache::flush();
            } elseif ($cacheDriver === 'redis') {
                // For Redis, use pattern matching to delete specific keys
                $redis = Cache::getStore()->getRedis();
                $keys = $redis->keys($pattern);
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            } else {
                // For other drivers (memcached, database), flush all
                // (not ideal but safe fallback)
                Cache::flush();
            }
        } catch (\Exception $e) {
            Log::warning('Cache invalidation failed', [
                'error' => $e->getMessage(),
                'student_id' => $studentId,
                'driver' => config('cache.default')
            ]);
            // Fail silently - graceful degradation
        }
    }
}
