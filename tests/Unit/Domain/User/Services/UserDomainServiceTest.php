<?php

namespace Tests\Unit\Domain\User\Services;

use App\Domain\User\Services\UserDomainService;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserType;
use App\Domain\User\ValueObjects\Password;
use App\Domain\User\ValueObjects\BirthDate;
use App\Domain\User\ValueObjects\Gender;
use App\Domain\User\ValueObjects\PersonName;
use App\Domain\User\ValueObjects\GymGoals;
use App\Domain\User\Entities\UserEntity;
use Tests\TestCase;
use Mockery as m;

class UserDomainServiceTest extends TestCase
{
    private $userRepository;
    private $userDomainService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = m::mock(UserRepositoryInterface::class);
        $this->userDomainService = new UserDomainService($this->userRepository);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function test_it_creates_student_user_with_gym_goals()
    {
        $email = new Email('student@example.com');

        $this->userRepository->shouldReceive('existsByEmail')
            ->with(m::on(function($emailArg) use ($email) {
                return $emailArg->equals($email);
            }))
            ->once()
            ->andReturn(false);

        $this->userRepository->shouldReceive('save')
            ->once()
            ->andReturn(new UserEntity(
                UserId::generate(),
                new Email('student@example.com'),
                new Password('password123'),
                new UserType('student'),
                new PersonName('John'),
                new PersonName('Doe'),
                new BirthDate('1990-01-01'),
                new Gender('male'),
                new GymGoals('Lose weight and gain muscle')
            ));

        $user = $this->userDomainService->createUser(
            'student@example.com',
            'password123',
            'student',
            'John',
            'Doe',
            '1990-01-01',
            'male',
            'Lose weight and gain muscle'
        );

        $this->assertInstanceOf(UserEntity::class, $user);
    }

    public function test_it_creates_trainer_user_without_gym_goals()
    {
        $email = new Email('trainer@example.com');

        $this->userRepository->shouldReceive('existsByEmail')
            ->once()
            ->andReturn(false);

        $this->userRepository->shouldReceive('save')
            ->once()
            ->andReturn(new UserEntity(
                UserId::generate(),
                new Email('trainer@example.com'),
                new Password('password123'),
                new UserType('trainer'),
                new PersonName('Jane'),
                new PersonName('Smith'),
                new BirthDate('1985-05-15'),
                new Gender('female'),
                new GymGoals(null)
            ));

        $user = $this->userDomainService->createUser(
            'trainer@example.com',
            'password123',
            'trainer',
            'Jane',
            'Smith',
            '1985-05-15',
            'female'
        );

        $this->assertInstanceOf(UserEntity::class, $user);
    }

    public function test_it_throws_exception_when_email_already_exists()
    {
        $email = new Email('existing@example.com');
        
        $this->userRepository->shouldReceive('existsByEmail')
            ->once()
            ->andReturn(true);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Ya existe una cuenta con este email');

        $this->userDomainService->createUser(
            'existing@example.com',
            'password123',
            'student',
            'John',
            'Doe',
            '1990-01-01',
            'male',
            'Get fit'
        );
    }

    public function test_it_throws_exception_when_student_has_no_gym_goals()
    {
        $this->userRepository->shouldReceive('existsByEmail')
            ->once()
            ->andReturn(false);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Students must provide gym goals');

        $this->userDomainService->createUser(
            'student@example.com',
            'password123',
            'student',
            'John',
            'Doe',
            '1990-01-01',
            'male'
        );
    }

    public function test_validate_minimum_age_returns_true_for_valid_age()
    {
        $birthDate = now()->subYears(18)->format('Y-m-d');
        
        $result = $this->userDomainService->validateMinimumAge($birthDate);
        
        $this->assertTrue($result);
    }

    public function test_validate_minimum_age_returns_false_for_under_age()
    {
        $birthDate = now()->subYears(15)->format('Y-m-d');
        
        $result = $this->userDomainService->validateMinimumAge($birthDate);
        
        $this->assertFalse($result);
    }

    public function test_is_email_unique_returns_true_when_email_not_exists()
    {
        $email = new Email('unique@example.com');
        
        $this->userRepository->shouldReceive('existsByEmail')
            ->once()
            ->andReturn(false);
        
        $result = $this->userDomainService->isEmailUnique($email);
        
        $this->assertTrue($result);
    }

    public function test_is_email_unique_returns_false_when_email_exists()
    {
        $email = new Email('existing@example.com');
        
        $this->userRepository->shouldReceive('existsByEmail')
            ->once()
            ->andReturn(true);
        
        $result = $this->userDomainService->isEmailUnique($email);
        
        $this->assertFalse($result);
    }

    // Edge cases
    public function test_it_throws_exception_for_invalid_user_type()
    {
        $this->userRepository->shouldReceive('existsByEmail')
            ->once()
            ->andReturn(false);

        $this->expectException(\InvalidArgumentException::class);

        $this->userDomainService->createUser(
            'user@example.com',
            'password123',
            'invalid_type',
            'John',
            'Doe',
            '1990-01-01',
            'male'
        );
    }

    public function test_validate_minimum_age_edge_case_exactly_16_years()
    {
        $birthDate = now()->subYears(16)->format('Y-m-d');
        
        $result = $this->userDomainService->validateMinimumAge($birthDate);
        
        $this->assertTrue($result);
    }
}