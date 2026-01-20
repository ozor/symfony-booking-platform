<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Value Object representing hashed password.
 */
final readonly class HashedPassword
{
    private function __construct(
        private string $value
    ) {
    }

    public static function fromHash(string $hash): self
    {
        if (empty($hash)) {
            throw new InvalidArgumentException('Password hash cannot be empty');
        }

        return new self($hash);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
