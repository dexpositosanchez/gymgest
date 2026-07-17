<?php

declare(strict_types=1);

namespace App\Application\ExerciseWeightHistory\Services;

use Illuminate\Support\Facades\Cache;

class WeightHistoryCacheService
{
    private const CACHE_PREFIX = 'weight_history';
    private const DEFAULT_TTL = 3600; // 1 hour

    /**
     * @param string $studentId
     * @param array<string, mixed> $params
     * @return string
     */
    public function getCacheKey(string $studentId, array $params): string
    {
        ksort($params);
        $paramsString = json_encode($params);
        return self::CACHE_PREFIX . ':' . $studentId . ':' . md5($paramsString);
    }

    /**
     * @param string $studentId
     * @param array<string, mixed> $params
     * @return mixed|null
     */
    public function get(string $studentId, array $params)
    {
        $key = $this->getCacheKey($studentId, $params);
        return Cache::get($key);
    }

    /**
     * @param string $studentId
     * @param array<string, mixed> $params
     * @param mixed $data
     * @param int $ttl
     * @return void
     */
    public function set(string $studentId, array $params, $data, int $ttl = self::DEFAULT_TTL): void
    {
        $key = $this->getCacheKey($studentId, $params);
        Cache::put($key, $data, $ttl);
    }

    public function invalidate(string $studentId): void
    {
        try {
            $cacheDriver = config('cache.default');
            $pattern = self::CACHE_PREFIX . ':' . $studentId . ':*';

            // Different strategies based on cache driver
            if (in_array($cacheDriver, ['array', 'file'])) {
                // For array/file cache, flush everything (tests or simple setups)
                Cache::flush();
            } elseif ($cacheDriver === 'redis') {
                // For Redis, use pattern matching to delete specific keys
                if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                    $redis = Cache::getStore()->connection();
                    $keys = $redis->keys($pattern);

                    if (!empty($keys)) {
                        $redis->del($keys);
                    }
                }
            } else {
                // For other drivers (memcached, database), flush all
                Cache::flush();
            }
        } catch (\Exception $e) {
            // Fail silently - graceful degradation
        }
    }
}
