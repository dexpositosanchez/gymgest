<?php

declare(strict_types=1);

namespace Tests\Unit\Application\RoutineAssignment;

use Tests\TestCase;
use App\Application\RoutineAssignment\Services\RoutineAssignmentCacheService;
use Illuminate\Support\Facades\Cache;
use Mockery;

class RoutineAssignmentCacheServiceTest extends TestCase
{
    private RoutineAssignmentCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = new RoutineAssignmentCacheService();
    }

    public function test_generates_consistent_cache_key_for_same_params(): void
    {
        $studentId = 'student-123';
        $params1 = ['page' => 1, 'per_page' => 10, 'gym_id' => 'gym-1'];
        $params2 = ['page' => 1, 'per_page' => 10, 'gym_id' => 'gym-1'];

        $key1 = $this->cacheService->getCacheKey($studentId, $params1);
        $key2 = $this->cacheService->getCacheKey($studentId, $params2);

        $this->assertEquals($key1, $key2);
        $this->assertStringStartsWith('student:routines:student-123:', $key1);
    }

    public function test_generates_different_cache_key_for_different_params(): void
    {
        $studentId = 'student-123';
        $params1 = ['page' => 1, 'per_page' => 10];
        $params2 = ['page' => 2, 'per_page' => 10];

        $key1 = $this->cacheService->getCacheKey($studentId, $params1);
        $key2 = $this->cacheService->getCacheKey($studentId, $params2);

        $this->assertNotEquals($key1, $key2);
    }

    public function test_retrieves_cached_data(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->with(Mockery::pattern('/^student:routines:/'))
            ->andReturn(['data' => 'test']);

        $result = $this->cacheService->get('student-123', ['page' => 1]);

        $this->assertEquals(['data' => 'test'], $result);
    }

    public function test_returns_null_when_cache_miss(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->andReturn(null);

        $result = $this->cacheService->get('student-123', ['page' => 1]);

        $this->assertNull($result);
    }

    public function test_stores_data_with_ttl(): void
    {
        $data = ['data' => 'test'];
        $ttl = 300;

        Cache::shouldReceive('put')
            ->once()
            ->with(Mockery::pattern('/^student:routines:/'), $data, $ttl)
            ->andReturn(true);

        $this->cacheService->set('student-123', ['page' => 1], $data, $ttl);

        $this->assertTrue(true); // If no exception, test passes
    }

    public function test_invalidates_all_student_keys(): void
    {
        // In array cache mode (tests), it should flush
        Cache::shouldReceive('flush')
            ->once()
            ->andReturn(true);

        $this->cacheService->invalidate('student-123');

        $this->assertTrue(true); // If no exception, test passes
    }

    public function test_handles_redis_connection_failure_gracefully(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->andThrow(new \Exception('Connection failed'));

        // Should not throw exception, return null instead
        $result = $this->cacheService->get('student-123', ['page' => 1]);

        $this->assertNull($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
