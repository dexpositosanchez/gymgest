<?php

namespace Tests\Unit\Infrastructure\Http\Middleware;

use App\Infrastructure\Http\Middleware\EnsureUserIsTrainer;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Mockery as m;

class EnsureUserIsTrainerTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function test_it_allows_trainer_users()
    {
        $middleware = new EnsureUserIsTrainer();
        $request = Request::create('/test', 'GET');

        $user = new UserEloquentModel();
        $user->user_type = 'trainer';

        JWTAuth::shouldReceive('parseToken')
            ->once()
            ->andReturnSelf();

        JWTAuth::shouldReceive('authenticate')
            ->once()
            ->andReturn($user);

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
    }

    public function test_it_blocks_student_users()
    {
        $middleware = new EnsureUserIsTrainer();
        $request = Request::create('/test', 'GET');

        $user = new UserEloquentModel();
        $user->user_type = 'student';

        JWTAuth::shouldReceive('parseToken')
            ->once()
            ->andReturnSelf();

        JWTAuth::shouldReceive('authenticate')
            ->once()
            ->andReturn($user);

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->status());
        $this->assertEquals('Esta aplicación es solo para entrenadores', $response->getData()->error);
    }

    public function test_it_blocks_unauthenticated_users()
    {
        $middleware = new EnsureUserIsTrainer();
        $request = Request::create('/test', 'GET');

        JWTAuth::shouldReceive('parseToken')
            ->once()
            ->andReturnSelf();

        JWTAuth::shouldReceive('authenticate')
            ->once()
            ->andThrow(new \Exception('Token not provided'));

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->status());
        $this->assertEquals('No autenticado', $response->getData()->error);
    }

    public function test_it_blocks_null_user()
    {
        $middleware = new EnsureUserIsTrainer();
        $request = Request::create('/test', 'GET');

        JWTAuth::shouldReceive('parseToken')
            ->once()
            ->andReturnSelf();

        JWTAuth::shouldReceive('authenticate')
            ->once()
            ->andReturn(null);

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->status());
        $this->assertEquals('Esta aplicación es solo para entrenadores', $response->getData()->error);
    }
}
