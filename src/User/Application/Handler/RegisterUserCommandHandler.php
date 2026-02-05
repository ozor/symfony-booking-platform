<?php

declare(strict_types=1);

namespace App\User\Application\Handler;

use App\User\Application\Command\RegisterUserCommand;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Service\PasswordHasherInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\PhoneNumber;
use App\User\Domain\ValueObject\UserRole;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handler for RegisterUserCommand.
 * Creates a new user in the system.
 */
#[AsMessageHandler]
final readonly class RegisterUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher
    ) {
    }

    public function __invoke(RegisterUserCommand $command): void
    {
        // Validate email
        $email = Email::fromString($command->email);

        // Check if user already exists
        if ($this->userRepository->findByEmail($email) !== null) {
            throw new InvalidArgumentException(
                sprintf('User with email %s already exists', $email->value())
            );
        }

        // Validate password
        if (empty($command->password)) {
            throw new InvalidArgumentException('Password cannot be empty');
        }

        if (strlen($command->password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long');
        }

        // Hash password
        $hashedPassword = $this->passwordHasher->hash($command->password);

        // Parse phone number if provided
        $phoneNumber = null;
        if ($command->phoneNumber !== null && $command->phoneNumber !== '') {
            $phoneNumber = PhoneNumber::fromString($command->phoneNumber);
        }

        // Determine roles (default to CUSTOMER if not provided)
        $roles = !empty($command->roles)
            ? array_map(
                fn($role) => is_string($role) ? UserRole::from($role) : $role,
                $command->roles
            )
            : [UserRole::CUSTOMER];

        // Create user entity
        $user = User::create(
            email: $email,
            password: $hashedPassword,
            firstName: $command->firstName ?: 'User',
            lastName: $command->lastName ?: '',
            roles: $roles,
            tenantId: $command->tenantId,
            phoneNumber: $phoneNumber
        );

        // Persist user
        $this->userRepository->save($user);
    }
}
