<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use Illuminate\Support\Facades\Hash;

class Password
{
    /** @var string */
    private $hashedValue;

    public function __construct(string $plainPassword, bool $alreadyHashed = false)
    {
        if ($alreadyHashed) {
            $this->hashedValue = $plainPassword;
            return;
        }

        if (strlen($plainPassword) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters long');
        }

        $this->hashedValue = Hash::make($plainPassword);
    }

    public static function fromHashed(string $hashedPassword): self
    {
        return new self($hashedPassword, true);
    }

    public function getHashedValue(): string
    {
        return $this->hashedValue;
    }

    public function verify(string $plainPassword): bool
    {
        return Hash::check($plainPassword, $this->hashedValue);
    }

    public function __toString(): string
    {
        return $this->hashedValue;
    }
}
