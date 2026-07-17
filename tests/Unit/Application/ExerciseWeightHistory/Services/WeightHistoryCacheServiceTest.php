<?php

declare(strict_types=1);

namespace Tests\Unit\Application\ExerciseWeightHistory\Services;

use App\Application\ExerciseWeightHistory\Services\WeightHistoryCacheService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WeightHistoryCacheServiceTest extends TestCase
{
    private WeightHistoryCacheService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WeightHistoryCacheService();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }

    public function test_generates_consistent_cache_key_for_same_params(): void
    {
        $studentId = '550e8400-e29b-41d4-a716-446655440000';
        $exerciseId = '660e8400-e29b-41d4-a716-446655440000';
        $reps = 10;

        $params = ['exercise_id' => $exerciseId, 'reps' => $reps];

        $key1 = $this->service->getCacheKey($studentId, $params);
        $key2 = $this->service->getCacheKey($studentId, $params);

        $this->assertEquals($key1, $key2);
    }

    public function test_generates_different_cache_key_for_different_params(): void
    {
        $studentId = '550e8400-e29b-41d4-a716-446655440000';

        $params1 = ['exercise_id' => '660e8400-e29b-41d4-a716-446655440000', 'reps' => 10];
        $params2 = ['exercise_id' => '660e8400-e29b-41d4-a716-446655440000', 'reps' => 12];

        $key1 = $this->service->getCacheKey($studentId, $params1);
        $key2 = $this->service->getCacheKey($studentId, $params2);

        $this->assertNotEquals($key1, $key2);
    }

    public function test_retrieves_cached_data(): void
    {
        $studentId = '550e8400-e29b-41d4-a716-446655440000';
        $params = ['exercise_id' => '660e8400-e29b-41d4-a716-446655440000', 'reps' => 10];
        $data = ['weight' => 75.0];

        $this->service->set($studentId, $params, $data);
        $result = $this->service->get($studentId, $params);

        $this->assertEquals($data, $result);
    }

    public function test_returns_null_when_cache_miss(): void
    {
        $studentId = '550e8400-e29b-41d4-a716-446655440000';
        $params = ['exercise_id' => '660e8400-e29b-41d4-a716-446655440000', 'reps' => 10];

        $result = $this->service->get($studentId, $params);

        $this->assertNull($result);
    }

    public function test_stores_data_with_ttl(): void
    {
        $studentId = '550e8400-e29b-41d4-a716-446655440000';
        $params = ['exercise_id' => '660e8400-e29b-41d4-a716-446655440000', 'reps' => 10];
        $data = ['weight' => 80.0];

        $this->service->set($studentId, $params, $data, 600);

        $result = $this->service->get($studentId, $params);
        $this->assertEquals($data, $result);
    }

    public function test_invalidates_all_student_keys(): void
    {
        $studentId = '550e8400-e29b-41d4-a716-446655440000';

        $params1 = ['exercise_id' => '660e8400-e29b-41d4-a716-446655440000', 'reps' => 10];
        $params2 = ['exercise_id' => '770e8400-e29b-41d4-a716-446655440000', 'reps' => 12];

        $this->service->set($studentId, $params1, ['weight' => 75.0]);
        $this->service->set($studentId, $params2, ['weight' => 80.0]);

        $this->service->invalidate($studentId);

        $this->assertNull($this->service->get($studentId, $params1));
        $this->assertNull($this->service->get($studentId, $params2));
    }
}
