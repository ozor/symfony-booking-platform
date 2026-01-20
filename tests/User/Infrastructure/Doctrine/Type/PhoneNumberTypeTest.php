<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Doctrine\Type;

use App\User\Domain\ValueObject\PhoneNumber;
use App\User\Infrastructure\Doctrine\Type\PhoneNumberType;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\TypesException;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

final class PhoneNumberTypeTest extends TestCase
{
    private PhoneNumberType $type;
    private PostgreSQLPlatform $platform;

    /**
     * @throws TypesException
     * @throws Exception
     */
    protected function setUp(): void
    {
        if (!Type::hasType('phone_number')) {
            Type::addType('phone_number', PhoneNumberType::class);
        }

        $type = Type::getType('phone_number');
        assert($type instanceof PhoneNumberType);

        $this->type = $type;
        $this->platform = new PostgreSQLPlatform();
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToDatabaseValue(): void
    {
        $phone = PhoneNumber::fromString('+79991234567');

        $result = $this->type->convertToDatabaseValue($phone, $this->platform);

        $this->assertSame('+79991234567', $result);
    }

    /**
     * @throws ConversionException
     */
    public function testConvertNullToDatabaseValue(): void
    {
        $result = $this->type->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($result);
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToPHPValue(): void
    {
        $result = $this->type->convertToPHPValue('+79991234567', $this->platform);

        $this->assertInstanceOf(PhoneNumber::class, $result);
        $this->assertSame('+79991234567', $result->value());
    }

    /**
     * @throws ConversionException
     */
    public function testConvertNullToPHPValue(): void
    {
        $result = $this->type->convertToPHPValue(null, $this->platform);

        $this->assertNull($result);
    }

    /**
     * @throws ConversionException
     */
    public function testConvertPhoneNumberToPHPValue(): void
    {
        $phone = PhoneNumber::fromString('+79991234567');

        $result = $this->type->convertToPHPValue($phone, $this->platform);

        $this->assertSame($phone, $result);
    }

    public function testGetName(): void
    {
        $this->assertSame('phone_number', $this->type->getName());
    }

    public function testRequiresSQLCommentHint(): void
    {
        $this->assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }
}
