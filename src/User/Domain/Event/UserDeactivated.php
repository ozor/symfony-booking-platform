<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

use App\User\Domain\ValueObject\UserId;
use DateTimeImmutable;

/**
 * Domain event: User was deactivated.
 */
final readonly class UserDeactivated
{
    public function __construct(
        public UserId $userId,
        public DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(UserId $userId): self
    {
        return new self($userId, new DateTimeImmutable());
    }
}
