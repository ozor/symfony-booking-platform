<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\PhoneNumber;
use App\User\Domain\ValueObject\UserId;
use DateTimeImmutable;

/**
 * Domain event: User email was changed.
 */
final readonly class UserPhoneNumberChanged
{
    public function __construct(
        public UserId $userId,
        public PhoneNumber $oldEmail,
        public PhoneNumber $newEmail,
        public DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(UserId $userId, PhoneNumber $oldPhoneNumber, PhoneNumber $newPhoneNumber): self
    {
        return new self(
            $userId,
            $oldPhoneNumber,
            $newPhoneNumber,
            new DateTimeImmutable()
        );
    }
}
