<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Doctrine\Type;

use App\User\Domain\ValueObject\HashedPassword;
use App\User\Infrastructure\Doctrine\Type\HashedPasswordType;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\TypesException;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

final class HashedPasswordTypeTest extends TestCase
{
    private HashedPasswordType $type;
    private PostgreSQLPlatform $platform;

    /**
     * @throws TypesException
     * @throws Exception
     */
    protected function setUp(): void
    {
        if (!Type::hasType('hashed_password')) {
            Type::addType('hashed_password', HashedPasswordType::class);
        }

        $type = Type::getType('hashed_password');
        assert($type instanceof HashedPasswordType);

        $this->type = $type;
        $this->platform = new PostgreSQLPlatform();
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToDatabaseValue(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        $password = HashedPassword::fromHash($hash);

        $result = $this->type->convertToDatabaseValue($password, $this->platform);

        $this->assertSame($hash, $result);
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
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $result = $this->type->convertToPHPValue($hash, $this->platform);

        $this->assertInstanceOf(HashedPassword::class, $result);
        $this->assertSame($hash, $result->value());
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
    public function testConvertHashedPasswordToPHPValue(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        $password = HashedPassword::fromHash($hash);

        $result = $this->type->convertToPHPValue($password, $this->platform);

        $this->assertSame($password, $result);
    }

    public function testGetName(): void
    {
        $this->assertSame('hashed_password', $this->type->getName());
    }

    public function testRequiresSQLCommentHint(): void
    {
        $this->assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }
}
