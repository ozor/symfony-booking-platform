<?php

declare(strict_types=1);

namespace App\Tests\User\Domain\ValueObject;

use App\User\Domain\ValueObject\UserId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UserIdTest extends TestCase
{
    public function testGenerateCreatesValidUuid(): void
    {
        $userId = UserId::generate();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $userId->value()
        );
    }

    public function testGenerateCreatesUniqueIds(): void
    {
        $userId1 = UserId::generate();
        $userId2 = UserId::generate();

        $this->assertFalse($userId1->equals($userId2));
    }

    public function testFromStringCreatesValidUserId(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $userId = UserId::fromString($uuid);

        $this->assertSame($uuid, $userId->value());
    }

    public function testFromStringThrowsExceptionForInvalidFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UserId format: invalid-uuid');

        UserId::fromString('invalid-uuid');
    }

    public function testEqualsReturnsTrueForSameValue(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $userId1 = UserId::fromString($uuid);
        $userId2 = UserId::fromString($uuid);

        $this->assertTrue($userId1->equals($userId2));
    }

    public function testEqualsReturnsFalseForDifferentValues(): void
    {
        $userId1 = UserId::generate();
        $userId2 = UserId::generate();

        $this->assertFalse($userId1->equals($userId2));
    }
}
