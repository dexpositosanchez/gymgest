<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GymStudent\ValueObjects;

use App\Domain\GymStudent\ValueObjects\QuotaExpiresAt;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class QuotaExpiresAtTest extends TestCase
{
    public function test_can_create_valid_future_date(): void
    {
        $futureDate = date('Y-m-d', strtotime('+30 days'));
        $quotaExpiresAt = new QuotaExpiresAt($futureDate);

        $this->assertEquals($futureDate, $quotaExpiresAt->getValue());
    }

    public function test_throws_exception_for_past_date_when_validating(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quota expiration date must be in the future');

        $pastDate = date('Y-m-d', strtotime('-1 day'));
        QuotaExpiresAt::createForEnrollment($pastDate);
    }

    public function test_throws_exception_for_today_when_validating(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quota expiration date must be in the future');

        $today = date('Y-m-d');
        QuotaExpiresAt::createForEnrollment($today);
    }

    public function test_accepts_tomorrow_when_validating(): void
    {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $quotaExpiresAt = QuotaExpiresAt::createForEnrollment($tomorrow);

        $this->assertEquals($tomorrow, $quotaExpiresAt->getValue());
    }

    public function test_can_create_without_validation_for_listing(): void
    {
        $pastDate = date('Y-m-d', strtotime('-10 days'));
        $quotaExpiresAt = new QuotaExpiresAt($pastDate);

        $this->assertEquals($pastDate, $quotaExpiresAt->getValue());
    }

    public function test_is_expired_returns_true_for_past_date(): void
    {
        $pastDate = date('Y-m-d', strtotime('-1 day'));
        $quotaExpiresAt = new QuotaExpiresAt($pastDate);

        $this->assertTrue($quotaExpiresAt->isExpired());
    }

    public function test_is_expired_returns_false_for_future_date(): void
    {
        $futureDate = date('Y-m-d', strtotime('+10 days'));
        $quotaExpiresAt = new QuotaExpiresAt($futureDate);

        $this->assertFalse($quotaExpiresAt->isExpired());
    }

    public function test_is_expired_returns_true_for_today(): void
    {
        $today = date('Y-m-d');
        $quotaExpiresAt = new QuotaExpiresAt($today);

        $this->assertTrue($quotaExpiresAt->isExpired());
    }

    public function test_is_expiring_soon_returns_true_for_date_within_7_days(): void
    {
        $date = date('Y-m-d', strtotime('+5 days'));
        $quotaExpiresAt = new QuotaExpiresAt($date);

        $this->assertTrue($quotaExpiresAt->isExpiringSoon());
    }

    public function test_is_expiring_soon_returns_false_for_date_after_7_days(): void
    {
        $date = date('Y-m-d', strtotime('+8 days'));
        $quotaExpiresAt = new QuotaExpiresAt($date);

        $this->assertFalse($quotaExpiresAt->isExpiringSoon());
    }

    public function test_is_expiring_soon_returns_true_for_exactly_7_days(): void
    {
        $date = date('Y-m-d', strtotime('+7 days'));
        $quotaExpiresAt = new QuotaExpiresAt($date);

        $this->assertTrue($quotaExpiresAt->isExpiringSoon());
    }

    public function test_is_expiring_soon_with_custom_days(): void
    {
        $date = date('Y-m-d', strtotime('+12 days'));
        $quotaExpiresAt = new QuotaExpiresAt($date);

        $this->assertTrue($quotaExpiresAt->isExpiringSoon(15));
        $this->assertFalse($quotaExpiresAt->isExpiringSoon(10));
    }

    public function test_is_expiring_soon_returns_false_for_expired_date(): void
    {
        $pastDate = date('Y-m-d', strtotime('-1 day'));
        $quotaExpiresAt = new QuotaExpiresAt($pastDate);

        $this->assertFalse($quotaExpiresAt->isExpiringSoon());
    }

    public function test_throws_exception_for_invalid_date_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid date format');

        new QuotaExpiresAt('invalid-date');
    }

    public function test_throws_exception_for_empty_date(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quota expiration date cannot be empty');

        new QuotaExpiresAt('');
    }
}
