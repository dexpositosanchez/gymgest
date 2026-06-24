<?php

namespace Tests\Unit\Middleware;

use App\Infrastructure\Http\Middleware\IdempotencyMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class IdempotencyMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushall();
    }

    public function test_post_request_without_header_processes_normally()
    {
        $middleware = new IdempotencyMiddleware();
        $request = Request::create('/api/test', 'POST');

        $next = function ($req) {
            return new Response('Original response', 201);
        };

        $response = $middleware->handle($request, $next);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Original response', $response->getContent());
    }

    public function test_post_request_with_invalid_uuid_returns_400()
    {
        $middleware = new IdempotencyMiddleware();
        $request = Request::create('/api/test', 'POST', [], [], [], [
            'HTTP_IDEMPOTENCY_KEY' => 'invalid-uuid-format'
        ]);

        $next = function ($req) {
            return new Response('Should not reach here', 200);
        };

        $response = $middleware->handle($request, $next);

        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Invalid Idempotency-Key format', $responseData['error']);
    }

    public function test_get_request_ignores_idempotency_header()
    {
        $middleware = new IdempotencyMiddleware();
        $validUuid = '550e8400-e29b-41d4-a716-446655440000';
        $request = Request::create('/api/test', 'GET', [], [], [], [
            'HTTP_IDEMPOTENCY_KEY' => $validUuid
        ]);

        $next = function ($req) {
            return new Response('GET response', 200);
        };

        $response = $middleware->handle($request, $next);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('GET response', $response->getContent());

        // Verify nothing was cached
        $this->assertNull(Redis::get("idempotency:{$validUuid}"));
    }

    public function test_head_request_ignores_idempotency_header()
    {
        $middleware = new IdempotencyMiddleware();
        $validUuid = '550e8400-e29b-41d4-a716-446655440001';
        $request = Request::create('/api/test', 'HEAD', [], [], [], [
            'HTTP_IDEMPOTENCY_KEY' => $validUuid
        ]);

        $next = function ($req) {
            return new Response('', 200);
        };

        $response = $middleware->handle($request, $next);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNull(Redis::get("idempotency:{$validUuid}"));
    }

    public function test_options_request_ignores_idempotency_header()
    {
        $middleware = new IdempotencyMiddleware();
        $validUuid = '550e8400-e29b-41d4-a716-446655440002';
        $request = Request::create('/api/test', 'OPTIONS', [], [], [], [
            'HTTP_IDEMPOTENCY_KEY' => $validUuid
        ]);

        $next = function ($req) {
            return new Response('', 204);
        };

        $response = $middleware->handle($request, $next);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull(Redis::get("idempotency:{$validUuid}"));
    }

    public function test_first_post_request_with_valid_header_executes_and_caches()
    {
        $middleware = new IdempotencyMiddleware();
        $validUuid = '550e8400-e29b-41d4-a716-446655440003';
        $request = Request::create('/api/test', 'POST', [], [], [], [
            'HTTP_IDEMPOTENCY_KEY' => $validUuid
        ]);

        $next = function ($req) {
            return response()->json(['message' => 'Created successfully'], 201);
        };

        $response = $middleware->handle($request, $next);

        $this->assertEquals(201, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Created successfully', $responseData['message']);

        // Verify response was cached
        $cached = Redis::get("idempotency:{$validUuid}");
        $this->assertNotNull($cached);

        $cachedData = json_decode($cached, true);
        $this->assertEquals(201, $cachedData['status']);
        $this->assertStringContainsString('Created successfully', $cachedData['body']);
    }

    public function test_second_post_request_with_same_key_returns_cached_response()
    {
        $middleware = new IdempotencyMiddleware();
        $validUuid = '550e8400-e29b-41d4-a716-446655440004';
        $request = Request::create('/api/test', 'POST', [], [], [], [
            'HTTP_IDEMPOTENCY_KEY' => $validUuid
        ]);

        $executionCount = 0;
        $next = function ($req) use (&$executionCount) {
            $executionCount++;
            return response()->json(['message' => 'Original', 'count' => $executionCount], 201);
        };

        // First request
        $response1 = $middleware->handle($request, $next);
        $this->assertEquals(1, $executionCount);

        // Second request with same key
        $response2 = $middleware->handle($request, $next);
        $this->assertEquals(1, $executionCount); // Should not increment

        // Verify both responses are identical
        $this->assertEquals($response1->getStatusCode(), $response2->getStatusCode());
        $this->assertEquals($response1->getContent(), $response2->getContent());

        // Verify second response has replay header
        $this->assertEquals('true', $response2->headers->get('X-Idempotent-Replayed'));
    }

    public function test_cached_response_includes_same_status_code_and_body()
    {
        $middleware = new IdempotencyMiddleware();
        $validUuid = '550e8400-e29b-41d4-a716-446655440005';
        $request = Request::create('/api/test', 'POST', [], [], [], [
            'HTTP_IDEMPOTENCY_KEY' => $validUuid
        ]);

        $next = function ($req) {
            return response()->json([
                'id' => 123,
                'name' => 'Test User',
                'email' => 'test@example.com'
            ], 201);
        };

        // First request
        $response1 = $middleware->handle($request, $next);
        $data1 = json_decode($response1->getContent(), true);

        // Second request
        $response2 = $middleware->handle($request, $next);
        $data2 = json_decode($response2->getContent(), true);

        $this->assertEquals(201, $response1->getStatusCode());
        $this->assertEquals(201, $response2->getStatusCode());
        $this->assertEquals($data1, $data2);
    }

    public function test_validation_error_response_is_also_cached()
    {
        $middleware = new IdempotencyMiddleware();
        $validUuid = '550e8400-e29b-41d4-a716-446655440006';
        $request = Request::create('/api/test', 'POST', [], [], [], [
            'HTTP_IDEMPOTENCY_KEY' => $validUuid
        ]);

        $next = function ($req) {
            return response()->json([
                'errors' => ['email' => ['Email is required']]
            ], 422);
        };

        // First request (validation error)
        $response1 = $middleware->handle($request, $next);
        $this->assertEquals(422, $response1->getStatusCode());

        // Second request - should return cached validation error
        $response2 = $middleware->handle($request, $next);
        $this->assertEquals(422, $response2->getStatusCode());
        $this->assertEquals('true', $response2->headers->get('X-Idempotent-Replayed'));
    }

    public function test_different_idempotency_keys_process_independently()
    {
        $middleware = new IdempotencyMiddleware();

        $uuid1 = '550e8400-e29b-41d4-a716-446655440007';
        $uuid2 = '550e8400-e29b-41d4-a716-446655440008';

        $request1 = Request::create('/api/test', 'POST', [], [], [], [
            'HTTP_IDEMPOTENCY_KEY' => $uuid1
        ]);

        $request2 = Request::create('/api/test', 'POST', [], [], [], [
            'HTTP_IDEMPOTENCY_KEY' => $uuid2
        ]);

        $counter = 0;
        $next = function ($req) use (&$counter) {
            $counter++;
            return response()->json(['count' => $counter], 201);
        };

        $response1 = $middleware->handle($request1, $next);
        $data1 = json_decode($response1->getContent(), true);

        $response2 = $middleware->handle($request2, $next);
        $data2 = json_decode($response2->getContent(), true);

        $this->assertEquals(1, $data1['count']);
        $this->assertEquals(2, $data2['count']);
        $this->assertNotEquals($data1['count'], $data2['count']);
    }

    public function test_server_error_is_not_cached()
    {
        $middleware = new IdempotencyMiddleware();
        $validUuid = '550e8400-e29b-41d4-a716-446655440009';
        $request = Request::create('/api/test', 'POST', [], [], [], [
            'HTTP_IDEMPOTENCY_KEY' => $validUuid
        ]);

        $executionCount = 0;
        $next = function ($req) use (&$executionCount) {
            $executionCount++;
            return response()->json(['error' => 'Internal server error'], 500);
        };

        // First request (500 error)
        $response1 = $middleware->handle($request, $next);
        $this->assertEquals(500, $response1->getStatusCode());
        $this->assertEquals(1, $executionCount);

        // Second request - should execute again (not cached)
        $response2 = $middleware->handle($request, $next);
        $this->assertEquals(500, $response2->getStatusCode());
        $this->assertEquals(2, $executionCount);
        $this->assertNull($response2->headers->get('X-Idempotent-Replayed'));
    }
}
