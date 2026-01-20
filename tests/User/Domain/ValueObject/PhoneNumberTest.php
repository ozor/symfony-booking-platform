<?php

declare(strict_types=1);

namespace App\Tests\User\Domain\ValueObject;

use App\User\Domain\ValueObject\PhoneNumber;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PhoneNumberTest extends TestCase
{
    public function testFromStringCreatesValidInternationalPhone(): void
    {
        $phone = PhoneNumber::fromString('+79991234567');

        $this->assertSame('+79991234567', $phone->value());
    }

    public function testFromStringCreatesValidLocalPhone(): void
    {
        $phone = PhoneNumber::fromString('89991234567');

        $this->assertSame('89991234567', $phone->value());
    }

    public function testFromStringTrimsWhitespace(): void
    {
        $phone = PhoneNumber::fromString('  +79991234567  ');

        $this->assertSame('+79991234567', $phone->value());
    }

    #[DataProvider('invalidPhoneNumberProvider')]
    public function testFromStringThrowsExceptionForInvalidFormat(string $invalidPhone): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid phone number format');

        PhoneNumber::fromString($invalidPhone);
    }

    public static function invalidPhoneNumberProvider(): array
    {
        return [
            'starts with zero' => ['+09991234567'],
            'too short' => ['+799'],
            'too long' => ['+79991234567890123456'],
            'contains letters' => ['+7999abc4567'],
            'invalid format' => ['invalid-phone'],
        ];
    }

    public function testEqualsReturnsTrueForSamePhone(): void
    {
        $phone1 = PhoneNumber::fromString('+79991234567');
        $phone2 = PhoneNumber::fromString('+79991234567');

        $this->assertTrue($phone1->equals($phone2));
    }
}
