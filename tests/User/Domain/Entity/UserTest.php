<?php

declare(strict_types=1);

namespace App\Tests\User\Domain\Entity;

use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\HashedPassword;
use App\User\Domain\ValueObject\PhoneNumber;
use App\User\Domain\ValueObject\UserId;
use App\User\Domain\ValueObject\UserRole;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testCreateGeneratesNewUser(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $user = User::create(
            Email::fromString('user@example.com'),
            HashedPassword::fromHash($hash),
            'John',
            'Doe'
        );

        $this->assertInstanceOf(UserId::class, $user->id());
        $this->assertSame('user@example.com', $user->email()->value());
        $this->assertSame('John', $user->firstName());
        $this->assertSame('Doe', $user->lastName());
        $this->assertSame('John Doe', $user->fullName());
        $this->assertNull($user->phoneNumber());
        $this->assertTrue($user->isActive());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->createdAt());
        $this->assertNull($user->updatedAt());
    }

    public function testCreateWithPhoneNumber(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $phone = PhoneNumber::fromString('+79991234567');

        $user = User::create(
            Email::fromString('user@example.com'),
            HashedPassword::fromHash($hash),
            'John',
            'Doe',
            [UserRole::CUSTOMER],
            null,
            $phone
        );

        $this->assertSame($phone, $user->phoneNumber());
    }

    public function testCreateWithDefaultCustomerRole(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $user = User::create(
            Email::fromString('user@example.com'),
            HashedPassword::fromHash($hash),
            'John',
            'Doe'
        );

        $this->assertTrue($user->hasRole(UserRole::CUSTOMER));
        $this->assertContains('ROLE_CUSTOMER', $user->getRoles());
    }

    public function testCreateWithAdminRole(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $user = User::create(
            Email::fromString('admin@example.com'),
            HashedPassword::fromHash($hash),
            'Admin',
            'User',
            [UserRole::ADMIN]
        );

        $this->assertTrue($user->isAdmin());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testChangeEmail(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $user = User::create(
            Email::fromString('old@example.com'),
            HashedPassword::fromHash($hash),
            'John',
            'Doe'
        );

        $newEmail = Email::fromString('new@example.com');
        $user->changeEmail($newEmail);

        $this->assertSame($newEmail, $user->email());
        $this->assertNotNull($user->updatedAt());
    }

    public function testChangePassword(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $user = User::create(
            Email::fromString('user@example.com'),
            HashedPassword::fromHash($hash),
            'John',
            'Doe'
        );

        $newHash = password_hash('new-password', PASSWORD_BCRYPT);
        $newPassword = HashedPassword::fromHash($newHash);
        $user->changePassword($newPassword);

        $this->assertTrue(password_verify('new-password', $user->getPassword()));
    }

    public function testUpdateProfile(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $user = User::create(
            Email::fromString('user@example.com'),
            HashedPassword::fromHash($hash),
            'John',
            'Doe'
        );

        $user->updateProfile('Jane', 'Smith');

        $this->assertSame('Jane', $user->firstName());
        $this->assertSame('Smith', $user->lastName());
        $this->assertSame('Jane Smith', $user->fullName());
        $this->assertNotNull($user->updatedAt());
    }

    public function testChangePhoneNumber(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $user = User::create(
            Email::fromString('user@example.com'),
            HashedPassword::fromHash($hash),
            'John',
            'Doe'
        );

        $phone = PhoneNumber::fromString('+79991234567');
        $user->changePhoneNumber($phone);

        $this->assertSame($phone, $user->phoneNumber());
        $this->assertNotNull($user->updatedAt());
    }

    public function testAddRole(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $user = User::create(
            Email::fromString('user@example.com'),
            HashedPassword::fromHash($hash),
            'John',
            'Doe'
        );

        $user->addRole(UserRole::MANAGER);

        $this->assertTrue($user->hasRole(UserRole::MANAGER));
        $this->assertTrue($user->isManager());
    }

    public function testRemoveRole(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $user = User::create(
            Email::fromString('user@example.com'),
            HashedPassword::fromHash($hash),
            'John',
            'Doe',
            [UserRole::CUSTOMER, UserRole::MANAGER]
        );

        $user->removeRole(UserRole::MANAGER);

        $this->assertFalse($user->hasRole(UserRole::MANAGER));
        $this->assertTrue($user->hasRole(UserRole::CUSTOMER));
    }

    public function testActivateAndDeactivate(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $user = User::create(
            Email::fromString('user@example.com'),
            HashedPassword::fromHash($hash),
            'John',
            'Doe'
        );

        $user->deactivate();
        $this->assertFalse($user->isActive());

        $user->activate();
        $this->assertTrue($user->isActive());
    }

    public function testAssignToTenant(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $user = User::create(
            Email::fromString('user@example.com'),
            HashedPassword::fromHash($hash),
            'John',
            'Doe'
        );

        $user->assignToTenant('tenant-123');

        $this->assertSame('tenant-123', $user->tenantId());
    }

    public function testGetRolesAlwaysIncludesRoleUser(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $user = User::create(
            Email::fromString('user@example.com'),
            HashedPassword::fromHash($hash),
            'John',
            'Doe',
            []
        );

        $this->assertContains('ROLE_USER', $user->getRoles());
    }
}
