<?php

declare(strict_types=1);

namespace App\Domain\RoutineAssignment\Services;

interface RoutineAssignmentCacheServiceInterface
{
    /**
     * Generate cache key for student routine list
     *
     * @param string $studentId
     * @param array $params
     * @return string
     */
    public function getCacheKey(string $studentId, array $params): string;

    /**
     * Get cached data
     *
     * @param string $studentId
     * @param array $params
     * @return array|null
     */
    public function get(string $studentId, array $params): ?array;

    /**
     * Store data in cache
     *
     * @param string $studentId
     * @param array $params
     * @param array $data
     * @param int $ttl
     * @return void
     */
    public function set(string $studentId, array $params, array $data, int $ttl = 300): void;

    /**
     * Invalidate all cache entries for a student
     *
     * @param string $studentId
     * @return void
     */
    public function invalidate(string $studentId): void;
}
