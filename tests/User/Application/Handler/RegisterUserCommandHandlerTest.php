<?php

declare(strict_types=1);

namespace App\Tests\User\Application\Handler;

use App\User\Application\Command\RegisterUserCommand;
use App\User\Application\Handler\RegisterUserCommandHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Service\PasswordHasherInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\HashedPassword;
use App\User\Domain\ValueObject\UserRole;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for RegisterUserCommandHandler.
 */
class RegisterUserCommandHandlerTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private PasswordHasherInterface $passwordHasher;
    private RegisterUserCommandHandler $handler;

    #[AllowMockObjectsWithoutExpectations]
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $this->handler = new RegisterUserCommandHandler(
            $this->userRepository,
            $this->passwordHasher
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSuccessfulUserRegistration(): void
    {
        // Arrange
        $command = new RegisterUserCommand(
            email: 'test@example.com',
            password: 'SecurePass123',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(fn($email) => $email instanceof Email && $email->value() === 'test@example.com'))
            ->willReturn(null);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hash')
            ->with('SecurePass123')
            ->willReturn(HashedPassword::fromHash('$2y$13$hashed'));

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (User $user) {
                return $user->email()->value() === 'test@example.com'
                    && $user->firstName() === 'John'
                    && $user->lastName() === 'Doe';
            }));

        // Act
        ($this->handler)($command);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRegistrationFailsWhenEmailAlreadyExists(): void
    {
        // Arrange
        $command = new RegisterUserCommand(
            email: 'existing@example.com',
            password: 'SecurePass123'
        );

        $existingUser = $this->createStub(User::class);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn($existingUser);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User with email existing@example.com already exists');

        // Act
        ($this->handler)($command);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRegistrationFailsWithEmptyPassword(): void
    {
        // Arrange
        $command = new RegisterUserCommand(
            email: 'test@example.com',
            password: ''
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn(null);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password cannot be empty');

        // Act
        ($this->handler)($command);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRegistrationFailsWithShortPassword(): void
    {
        // Arrange
        $command = new RegisterUserCommand(
            email: 'test@example.com',
            password: 'short'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn(null);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must be at least 8 characters long');

        // Act
        ($this->handler)($command);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRegistrationFailsWithInvalidEmail(): void
    {
        // Arrange
        $command = new RegisterUserCommand(
            email: 'invalid-email',
            password: 'SecurePass123'
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        // Act
        ($this->handler)($command);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRegistrationWithCustomRoles(): void
    {
        // Arrange
        $command = new RegisterUserCommand(
            email: 'admin@example.com',
            password: 'SecurePass123',
            firstName: 'Admin',
            lastName: 'User',
            roles: [UserRole::ADMIN]
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn(null);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hash')
            ->willReturn(HashedPassword::fromHash('$2y$13$hashed'));

        $this->userRepository
            ->expects($this->once())
            ->method('save');

        // Act
        ($this->handler)($command);
    }
}
