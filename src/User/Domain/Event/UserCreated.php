<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;

/**
 * Domain event: User was created.
 */
final readonly class UserCreated
{
    public function __construct(
        public UserId $userId,
        public Email $email,
        public string $firstName,
        public string $lastName,
        public ?string $tenantId,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        UserId $userId,
        Email $email,
        string $firstName,
        string $lastName,
        ?string $tenantId
    ): self {
        return new self(
            $userId,
            $email,
            $firstName,
            $lastName,
            $tenantId,
            new \DateTimeImmutable()
        );
    }
}
