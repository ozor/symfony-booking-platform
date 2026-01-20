<?php

declare(strict_types=1);

namespace App\Tests\User\Domain\ValueObject;

use App\User\Domain\ValueObject\HashedPassword;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class HashedPasswordTest extends TestCase
{
    public function testFromHashCreatesValidPassword(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        $password = HashedPassword::fromHash($hash);

        $this->assertSame($hash, $password->value());
    }

    public function testFromHashThrowsExceptionForEmptyHash(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password hash cannot be empty');

        HashedPassword::fromHash('');
    }

    public function testValueReturnsHash(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        $password = HashedPassword::fromHash($hash);

        $this->assertSame($hash, $password->value());
    }

    public function testToStringReturnsHash(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        $password = HashedPassword::fromHash($hash);

        $this->assertSame($hash, (string) $password);
        $this->assertSame($password->value(), (string) $password);
    }
}
