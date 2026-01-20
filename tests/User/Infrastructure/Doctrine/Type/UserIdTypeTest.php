<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Doctrine\Type;

use App\User\Domain\ValueObject\UserId;
use App\User\Infrastructure\Doctrine\Type\UserIdType;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\TypesException;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

final class UserIdTypeTest extends TestCase
{
    private UserIdType $type;
    private PostgreSQLPlatform $platform;

    /**
     * @throws Exception
     * @throws TypesException
     */
    protected function setUp(): void
    {
        if (!Type::hasType('user_id')) {
            Type::addType('user_id', UserIdType::class);
        }

        $type = Type::getType('user_id');
        assert($type instanceof UserIdType);

        $this->type = $type;
        $this->platform = new PostgreSQLPlatform();
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToDatabaseValue(): void
    {
        $userId = UserId::generate();

        $result = $this->type->convertToDatabaseValue($userId, $this->platform);

        $this->assertSame($userId->value(), $result);
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
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $result = $this->type->convertToPHPValue($uuid, $this->platform);

        $this->assertInstanceOf(UserId::class, $result);
        $this->assertSame($uuid, $result->value());
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
    public function testConvertUserIdToPHPValue(): void
    {
        $userId = UserId::generate();

        $result = $this->type->convertToPHPValue($userId, $this->platform);

        $this->assertSame($userId, $result);
    }

    public function testGetName(): void
    {
        $this->assertSame('user_id', $this->type->getName());
    }

    public function testRequiresSQLCommentHint(): void
    {
        $this->assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }
}
