<?php

declare(strict_types=1);

namespace App\User\Application\Command;

/**
 * Command to register a new user.
 */
final readonly class RegisterUserCommand
{
    public function __construct(
        public string $email,
        public string $password,
        public string $firstName = '',
        public string $lastName = '',
        public ?string $phoneNumber = null,
        public ?string $tenantId = null,
        public array $roles = []
    ) {
    }
}
