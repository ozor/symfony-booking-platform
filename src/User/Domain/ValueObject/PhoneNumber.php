<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Value Object representing user phone number.
 */
final readonly class PhoneNumber
{
    private function __construct(
        private string $value
    ) {
    }

    public static function fromString(string $phoneNumber): self
    {
        $phoneNumber = trim($phoneNumber);

        // Простая проверка: международный формат +[цифры] или локальный формат
        if (!preg_match('/^\+?[1-9]\d{4,14}$/', $phoneNumber)) {
            throw new InvalidArgumentException(
                sprintf('Invalid phone number format: %s', $phoneNumber)
            );
        }

        return new self($phoneNumber);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
