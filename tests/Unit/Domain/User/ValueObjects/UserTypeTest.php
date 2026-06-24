<?php

namespace Tests\Unit\Domain\User\ValueObjects;

use App\Domain\User\ValueObjects\UserType;
use PHPUnit\Framework\TestCase;

class UserTypeTest extends TestCase
{
    public function test_it_creates_trainer_type()
    {
        $userType = new UserType(UserType::TRAINER);
        $this->assertEquals('trainer', $userType->getValue());
        $this->assertTrue($userType->isTrainer());
        $this->assertFalse($userType->isStudent());
    }

    public function test_it_creates_student_type()
    {
        $userType = new UserType(UserType::STUDENT);
        $this->assertEquals('student', $userType->getValue());
        $this->assertTrue($userType->isStudent());
        $this->assertFalse($userType->isTrainer());
    }

    public function test_it_throws_exception_for_invalid_type()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid user type. Must be trainer or student');
        
        new UserType('invalid');
    }

    public function test_it_throws_exception_for_empty_type()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new UserType('');
    }

    public function test_equals_returns_true_for_same_type()
    {
        $type1 = new UserType(UserType::TRAINER);
        $type2 = new UserType(UserType::TRAINER);
        
        $this->assertTrue($type1->equals($type2));
    }

    public function test_equals_returns_false_for_different_type()
    {
        $type1 = new UserType(UserType::TRAINER);
        $type2 = new UserType(UserType::STUDENT);
        
        $this->assertFalse($type1->equals($type2));
    }

    public function test_get_valid_types_returns_all_types()
    {
        $validTypes = UserType::getValidTypes();
        
        $this->assertContains('trainer', $validTypes);
        $this->assertContains('student', $validTypes);
        $this->assertCount(2, $validTypes);
    }

    public function test_to_string_returns_type_value()
    {
        $userType = new UserType(UserType::TRAINER);
        
        $this->assertEquals('trainer', (string) $userType);
    }

    // Edge cases
    public function test_it_throws_exception_for_case_sensitive_invalid_type()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new UserType('TRAINER'); // Should be lowercase
    }

    public function test_it_throws_exception_for_null_type()
    {
        $this->expectException(\InvalidArgumentException::class);

        new UserType(null);
    }
}