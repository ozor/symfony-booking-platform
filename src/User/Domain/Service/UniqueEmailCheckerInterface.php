<?php

declare(strict_types=1);

namespace App\User\Domain\Service;

use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;

/**
 * Domain service for checking email uniqueness.
 * Used to enforce business rule: email must be unique across all users.
 */
interface UniqueEmailCheckerInterface
{
    /**
     * Check if email is already taken by another user.
     *
     * @param Email $email Email to check
     * @param UserId|null $excludeUserId Optional user ID to exclude from check (for updates)
     */
    public function isEmailTaken(Email $email, ?UserId $excludeUserId = null): bool;
}
