<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use DateTimeImmutable;

/**
 * Domain event: User email was changed.
 */
final readonly class UserEmailChanged
{
    public function __construct(
        public UserId $userId,
        public Email $oldEmail,
        public Email $newEmail,
        public DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(UserId $userId, Email $oldEmail, Email $newEmail): self
    {
        return new self(
            $userId,
            $oldEmail,
            $newEmail,
            new DateTimeImmutable()
        );
    }
}
