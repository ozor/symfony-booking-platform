<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Security;

use App\User\Domain\Service\PasswordHasherInterface;
use App\User\Domain\ValueObject\HashedPassword;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Symfony implementation of PasswordHasherInterface.
 */
final readonly class SymfonyPasswordHasher implements PasswordHasherInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function hash(string $plainPassword): HashedPassword
    {
        // Create a temporary user-like object for hashing
        $tempUser = new class implements PasswordAuthenticatedUserInterface {
            public function getPassword(): ?string
            {
                return null;
            }
        };

        $hashedValue = $this->passwordHasher->hashPassword($tempUser, $plainPassword);

        return HashedPassword::fromHash($hashedValue);
    }

    public function verify(HashedPassword $hashedPassword, string $plainPassword): bool
    {
        // Create a temporary user-like object with the hashed password
        $tempUser = new readonly class($hashedPassword) implements PasswordAuthenticatedUserInterface {
            public function __construct(private HashedPassword $password)
            {
            }

            public function getPassword(): ?string
            {
                return $this->password->value();
            }
        };

        return $this->passwordHasher->isPasswordValid($tempUser, $plainPassword);
    }

    public function needsRehash(HashedPassword $hashedPassword): bool
    {
        $tempUser = new readonly class($hashedPassword) implements PasswordAuthenticatedUserInterface {
            public function __construct(private HashedPassword $password)
            {
            }

            public function getPassword(): ?string
            {
                return $this->password->value();
            }
        };

        return $this->passwordHasher->needsRehash($tempUser);
    }
}
