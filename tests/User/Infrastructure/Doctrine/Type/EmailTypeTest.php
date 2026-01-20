<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Doctrine\Type;

use App\User\Domain\ValueObject\Email;
use App\User\Infrastructure\Doctrine\Type\EmailType;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\TypesException;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

final class EmailTypeTest extends TestCase
{
    private EmailType $type;
    private PostgreSQLPlatform $platform;

    /**
     * @throws Exception
     * @throws TypesException
     */
    protected function setUp(): void
    {
        if (!Type::hasType('email')) {
            Type::addType('email', EmailType::class);
        }

        $type = Type::getType('email');
        assert($type instanceof EmailType);

        $this->type = $type;
        $this->platform = new PostgreSQLPlatform();
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToDatabaseValue(): void
    {
        $email = Email::fromString('user@example.com');

        $result = $this->type->convertToDatabaseValue($email, $this->platform);

        $this->assertSame('user@example.com', $result);
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
        $result = $this->type->convertToPHPValue('user@example.com', $this->platform);

        $this->assertInstanceOf(Email::class, $result);
        $this->assertSame('user@example.com', $result->value());
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
    public function testConvertEmailToPHPValue(): void
    {
        $email = Email::fromString('user@example.com');

        $result = $this->type->convertToPHPValue($email, $this->platform);

        $this->assertSame($email, $result);
    }

    public function testGetName(): void
    {
        $this->assertSame('email', $this->type->getName());
    }

    public function testRequiresSQLCommentHint(): void
    {
        $this->assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }
}
