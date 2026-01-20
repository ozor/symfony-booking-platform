<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Service;

use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Service\UniqueEmailCheckerInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;

/**
 * Domain service implementation for checking email uniqueness.
 */
final readonly class UniqueEmailChecker implements UniqueEmailCheckerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function isEmailTaken(Email $email, ?UserId $excludeUserId = null): bool
    {
        $existingUser = $this->userRepository->findByEmail($email);

        if ($existingUser === null) {
            return false;
        }

        // If we're excluding a user (e.g., during update), check if it's the same user
        if ($excludeUserId !== null && $existingUser->id()->equals($excludeUserId)) {
            return false;
        }

        return true;
    }
}
