<?php

declare(strict_types=1);

namespace Tests\Unit\Application\GymStudent;

use App\Application\GymStudent\UseCases\ListStudentGymsUseCase;
use App\Application\RoutineAssignment\Services\RoutineAssignmentCacheService;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

class ListStudentGymsUseCaseTest extends TestCase
{
    private GymStudentRepositoryInterface $repository;
    private RoutineAssignmentCacheService $cacheService;
    private ListStudentGymsUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(GymStudentRepositoryInterface::class);
        $this->cacheService = $this->createMock(RoutineAssignmentCacheService::class);
        $this->useCase = new ListStudentGymsUseCase(
            $this->repository,
            $this->cacheService
        );
    }

    public function test_execute_returns_gyms_array(): void
    {
        // Arrange
        $studentId = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';
        $gymData = [
            (object) [
                'enrollment_id' => 'enrollment-1',
                'enrolled_at' => '2026-01-15 10:00:00',
                'quota_expires_at' => '2026-12-31',
                'gym_id' => 'gym-1',
                'gym_name' => 'Gimnasio Test',
                'gym_address' => 'Calle 1',
                'gym_locality' => 'Madrid',
                'gym_province' => 'Madrid',
                'gym_country' => 'España',
                'is_personal_training' => false,
                'trainer_id' => 'trainer-1',
                'trainer_name' => 'Juan',
                'trainer_last_name' => 'García',
                'trainer_email' => 'juan@example.com',
            ],
        ];

        $this->cacheService->expects($this->once())
            ->method('get')
            ->with($studentId, ['type' => 'gyms'])
            ->willReturn(null); // No cache

        $this->repository->expects($this->once())
            ->method('findActiveGymsByStudent')
            ->with($this->callback(function ($userId) use ($studentId) {
                return $userId instanceof UserId && $userId->getValue() === $studentId;
            }))
            ->willReturn($gymData);

        $this->cacheService->expects($this->once())
            ->method('set')
            ->with($studentId, ['type' => 'gyms'], $this->isType('array'));

        // Act
        $result = $this->useCase->execute($studentId);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('enrollment-1', $result[0]['enrollment_id']);
        $this->assertEquals('Gimnasio Test', $result[0]['gym']['name']);
        $this->assertEquals('Juan García', $result[0]['trainer']['name']);
    }

    public function test_calculates_quota_status_active(): void
    {
        // Arrange: Quota expires in 30 days
        $studentId = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';
        $gymData = [
            (object) [
                'enrollment_id' => 'enrollment-1',
                'enrolled_at' => '2026-01-15 10:00:00',
                'quota_expires_at' => now()->addDays(30)->toDateString(),
                'gym_id' => 'gym-1',
                'gym_name' => 'Gimnasio Test',
                'gym_address' => 'Calle 1',
                'gym_locality' => 'Madrid',
                'gym_province' => 'Madrid',
                'gym_country' => 'España',
                'is_personal_training' => false,
                'trainer_id' => 'trainer-1',
                'trainer_name' => 'Juan',
                'trainer_last_name' => 'García',
                'trainer_email' => 'juan@example.com',
            ],
        ];

        $this->cacheService->method('get')->willReturn(null);
        $this->repository->method('findActiveGymsByStudent')->willReturn($gymData);

        // Act
        $result = $this->useCase->execute($studentId);

        // Assert
        $this->assertEquals('active', $result[0]['quota_status']);
    }

    public function test_calculates_quota_status_expiring_soon(): void
    {
        // Arrange: Quota expires in 5 days
        $studentId = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';
        $gymData = [
            (object) [
                'enrollment_id' => 'enrollment-1',
                'enrolled_at' => '2026-01-15 10:00:00',
                'quota_expires_at' => now()->addDays(5)->toDateString(),
                'gym_id' => 'gym-1',
                'gym_name' => 'Gimnasio Test',
                'gym_address' => 'Calle 1',
                'gym_locality' => 'Madrid',
                'gym_province' => 'Madrid',
                'gym_country' => 'España',
                'is_personal_training' => false,
                'trainer_id' => 'trainer-1',
                'trainer_name' => 'Juan',
                'trainer_last_name' => 'García',
                'trainer_email' => 'juan@example.com',
            ],
        ];

        $this->cacheService->method('get')->willReturn(null);
        $this->repository->method('findActiveGymsByStudent')->willReturn($gymData);

        // Act
        $result = $this->useCase->execute($studentId);

        // Assert
        $this->assertEquals('expiring_soon', $result[0]['quota_status']);
    }

    public function test_calculates_quota_status_expired(): void
    {
        // Arrange: Quota expired 10 days ago
        $studentId = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';
        $gymData = [
            (object) [
                'enrollment_id' => 'enrollment-1',
                'enrolled_at' => '2026-01-15 10:00:00',
                'quota_expires_at' => now()->subDays(10)->toDateString(),
                'gym_id' => 'gym-1',
                'gym_name' => 'Gimnasio Test',
                'gym_address' => 'Calle 1',
                'gym_locality' => 'Madrid',
                'gym_province' => 'Madrid',
                'gym_country' => 'España',
                'is_personal_training' => false,
                'trainer_id' => 'trainer-1',
                'trainer_name' => 'Juan',
                'trainer_last_name' => 'García',
                'trainer_email' => 'juan@example.com',
            ],
        ];

        $this->cacheService->method('get')->willReturn(null);
        $this->repository->method('findActiveGymsByStudent')->willReturn($gymData);

        // Act
        $result = $this->useCase->execute($studentId);

        // Assert
        $this->assertEquals('expired', $result[0]['quota_status']);
    }

    public function test_maps_to_dto_structure(): void
    {
        // Arrange
        $studentId = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';
        $gymData = [
            (object) [
                'enrollment_id' => 'enrollment-1',
                'enrolled_at' => '2026-01-15 10:00:00',
                'quota_expires_at' => '2026-12-31',
                'gym_id' => 'gym-1',
                'gym_name' => 'Gimnasio Test',
                'gym_address' => 'Calle Principal 123',
                'gym_locality' => 'Madrid',
                'gym_province' => 'Madrid',
                'gym_country' => 'España',
                'is_personal_training' => false,
                'trainer_id' => 'trainer-1',
                'trainer_name' => 'Juan',
                'trainer_last_name' => 'García',
                'trainer_email' => 'juan@example.com',
            ],
        ];

        $this->cacheService->method('get')->willReturn(null);
        $this->repository->method('findActiveGymsByStudent')->willReturn($gymData);

        // Act
        $result = $this->useCase->execute($studentId);

        // Assert: Verify structure
        $this->assertArrayHasKey('enrollment_id', $result[0]);
        $this->assertArrayHasKey('enrolled_at', $result[0]);
        $this->assertArrayHasKey('quota_expires_at', $result[0]);
        $this->assertArrayHasKey('quota_status', $result[0]);
        $this->assertArrayHasKey('gym', $result[0]);
        $this->assertArrayHasKey('trainer', $result[0]);

        // Verify gym structure
        $gym = $result[0]['gym'];
        $this->assertArrayHasKey('id', $gym);
        $this->assertArrayHasKey('name', $gym);
        $this->assertArrayHasKey('address', $gym);
        $this->assertArrayHasKey('locality', $gym);
        $this->assertArrayHasKey('province', $gym);
        $this->assertArrayHasKey('country', $gym);
        $this->assertArrayHasKey('is_personal_training', $gym);

        // Verify trainer structure
        $trainer = $result[0]['trainer'];
        $this->assertArrayHasKey('id', $trainer);
        $this->assertArrayHasKey('name', $trainer);
        $this->assertArrayHasKey('email', $trainer);
    }

    public function test_uses_cache_when_available(): void
    {
        // Arrange
        $studentId = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';
        $cachedData = [
            [
                'enrollment_id' => 'cached-enrollment',
                'enrolled_at' => '2026-01-15',
                'quota_expires_at' => '2026-12-31',
                'quota_status' => 'active',
                'gym' => ['id' => 'gym-1', 'name' => 'Cached Gym'],
                'trainer' => ['id' => 'trainer-1', 'name' => 'Cached Trainer'],
            ],
        ];

        $this->cacheService->expects($this->once())
            ->method('get')
            ->with($studentId, ['type' => 'gyms'])
            ->willReturn($cachedData);

        // Repository should NOT be called when cache is available
        $this->repository->expects($this->never())
            ->method('findActiveGymsByStudent');

        // Act
        $result = $this->useCase->execute($studentId);

        // Assert
        $this->assertEquals($cachedData, $result);
    }

    public function test_stores_in_cache_after_query(): void
    {
        // Arrange
        $studentId = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';
        $gymData = [
            (object) [
                'enrollment_id' => 'enrollment-1',
                'enrolled_at' => '2026-01-15 10:00:00',
                'quota_expires_at' => '2026-12-31',
                'gym_id' => 'gym-1',
                'gym_name' => 'Gimnasio Test',
                'gym_address' => 'Calle 1',
                'gym_locality' => 'Madrid',
                'gym_province' => 'Madrid',
                'gym_country' => 'España',
                'is_personal_training' => false,
                'trainer_id' => 'trainer-1',
                'trainer_name' => 'Juan',
                'trainer_last_name' => 'García',
                'trainer_email' => 'juan@example.com',
            ],
        ];

        $this->cacheService->method('get')->willReturn(null);
        $this->repository->method('findActiveGymsByStudent')->willReturn($gymData);

        // Assert: Cache set is called with correct data
        $this->cacheService->expects($this->once())
            ->method('set')
            ->with(
                $studentId,
                ['type' => 'gyms'],
                $this->callback(function ($data) {
                    return is_array($data) && count($data) === 1 && $data[0]['enrollment_id'] === 'enrollment-1';
                })
            );

        // Act
        $this->useCase->execute($studentId);
    }

    public function test_returns_empty_array_when_no_gyms(): void
    {
        // Arrange
        $studentId = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';

        $this->cacheService->method('get')->willReturn(null);
        $this->repository->method('findActiveGymsByStudent')->willReturn([]);

        // Act
        $result = $this->useCase->execute($studentId);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }
}
