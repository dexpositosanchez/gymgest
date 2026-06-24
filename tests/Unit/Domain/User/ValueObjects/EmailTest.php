<?php

namespace Tests\Unit\Domain\User\ValueObjects;

use App\Domain\User\ValueObjects\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function test_it_creates_valid_email()
    {
        $email = new Email('user@example.com');
        $this->assertEquals('user@example.com', $email->getValue());
    }

    public function test_it_normalizes_email()
    {
        $email = new Email('  USER@EXAMPLE.COM  ');
        $this->assertEquals('user@example.com', $email->getValue());
    }

    public function test_it_throws_exception_for_invalid_email()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');
        
        new Email('invalid-email');
    }

    public function test_it_throws_exception_for_empty_email()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new Email('');
    }

    public function test_equals_returns_true_for_same_email()
    {
        $email1 = new Email('user@example.com');
        $email2 = new Email('user@example.com');
        
        $this->assertTrue($email1->equals($email2));
    }

    public function test_equals_returns_false_for_different_email()
    {
        $email1 = new Email('user1@example.com');
        $email2 = new Email('user2@example.com');
        
        $this->assertFalse($email1->equals($email2));
    }

    public function test_to_string_returns_email_value()
    {
        $email = new Email('user@example.com');
        
        $this->assertEquals('user@example.com', (string) $email);
    }

    // Edge cases
    public function test_it_handles_long_emails()
    {
        $longEmail = 'verylongusernamethatcouldpotentiallycauseissues@verylongdomainname.com';
        $email = new Email($longEmail);
        $this->assertEquals(strtolower($longEmail), $email->getValue());
    }
}