<?php

declare(strict_types=1);

namespace App\Domain\ExerciseWeightHistory\Services;

interface WeightHistoryCacheServiceInterface
{
    /**
     * Get cache key for weight history
     *
     * @param string $studentId
     * @param array<string, mixed> $params
     * @return string
     */
    public function getCacheKey(string $studentId, array $params): string;

    /**
     * Get cached weight history data
     *
     * @param string $studentId
     * @param array<string, mixed> $params
     * @return mixed|null
     */
    public function get(string $studentId, array $params);

    /**
     * Store weight history data in cache
     *
     * @param string $studentId
     * @param array<string, mixed> $params
     * @param mixed $data
     * @param int $ttl
     * @return void
     */
    public function set(string $studentId, array $params, $data, int $ttl = 3600): void;

    /**
     * Invalidate all weight history cache for a student
     *
     * @param string $studentId
     * @return void
     */
    public function invalidate(string $studentId): void;
}
