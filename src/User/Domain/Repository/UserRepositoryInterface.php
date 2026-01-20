<?php

declare(strict_types=1);

namespace App\User\Domain\Repository;

use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;

/**
 * Repository interface for User aggregate root.
 * Following DDD principles - interface in Domain, implementation in Infrastructure.
 */
interface UserRepositoryInterface
{
    /**
     * Save (persist or update) a user.
     */
    public function save(User $user): void;

    /**
     * Find user by unique identifier.
     */
    public function findById(UserId $id): ?User;

    /**
     * Find user by email address.
     */
    public function findByEmail(Email $email): ?User;

    /**
     * Find all users belonging to a specific tenant.
     *
     * @return array<User>
     */
    public function findByTenantId(string $tenantId): array;

    /**
     * Find all active users.
     *
     * @return array<User>
     */
    public function findAllActive(): array;

    /**
     * Find all users (active and inactive).
     *
     * @return array<User>
     */
    public function findAll(): array;

    /**
     * Delete a user.
     */
    public function delete(User $user): void;

    /**
     * Check if user with given email exists.
     */
    public function existsByEmail(Email $email): bool;

    /**
     * Generate next unique identifier.
     * Following DDD pattern for identity generation.
     */
    public function nextIdentity(): UserId;
}
