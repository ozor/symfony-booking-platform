<?php

declare(strict_types=1);

namespace App\Tests\User\Domain\ValueObject;

use App\User\Domain\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testFromStringCreatesValidEmail(): void
    {
        $email = Email::fromString('user@example.com');

        $this->assertSame('user@example.com', $email->value());
    }

    public function testFromStringTrimsWhitespace(): void
    {
        $email = Email::fromString('  user@example.com  ');

        $this->assertSame('user@example.com', $email->value());
    }

    public function testFromStringThrowsExceptionForInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        Email::fromString('invalid-email');
    }

    public function testDomainReturnsCorrectDomain(): void
    {
        $email = Email::fromString('user@example.com');

        $this->assertSame('example.com', $email->domain());
    }

    public function testEqualsReturnsTrueForSameEmail(): void
    {
        $email1 = Email::fromString('user@example.com');
        $email2 = Email::fromString('user@example.com');

        $this->assertTrue($email1->equals($email2));
    }

    public function testEqualsReturnsFalseForDifferentEmails(): void
    {
        $email1 = Email::fromString('user1@example.com');
        $email2 = Email::fromString('user2@example.com');

        $this->assertFalse($email1->equals($email2));
    }
}
