<?php

declare(strict_types=1);

namespace App\User\Domain\Service;

use App\User\Domain\ValueObject\HashedPassword;

/**
 * Domain service for password operations.
 * Implementation will be in Infrastructure layer.
 */
interface PasswordHasherInterface
{
    /**
     * Hash a plain text password.
     */
    public function hash(string $plainPassword): HashedPassword;

    /**
     * Verify if plain password matches hashed password.
     */
    public function verify(HashedPassword $hashedPassword, string $plainPassword): bool;

    /**
     * Check if password needs rehashing (algorithm changed, cost changed, etc.).
     */
    public function needsRehash(HashedPassword $hashedPassword): bool;
}
