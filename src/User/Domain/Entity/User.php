<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\HashedPassword;
use App\User\Domain\ValueObject\PhoneNumber;
use App\User\Domain\ValueObject\UserId;
use App\User\Domain\ValueObject\UserRole;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User entity - aggregate root for User domain.
 */
#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\Index(name: 'idx_user_email', columns: ['email'])]
#[ORM\Index(name: 'idx_user_tenant', columns: ['tenant_id'])]
#[ORM\Index(name: 'idx_user_active', columns: ['is_active'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'user_id')]
    private UserId $id;

    #[ORM\Column(type: 'email', unique: true)]
    private Email $email;

    #[ORM\Column(type: 'hashed_password')]
    private HashedPassword $password;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $firstName;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $lastName;

    #[ORM\Column(type: 'phone_number', nullable: true)]
    private ?PhoneNumber $phoneNumber;

    /**
     * @var array<UserRole>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isActive = true;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $tenantId;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $updatedAt;

    private function __construct(
        UserId $id,
        Email $email,
        HashedPassword $password,
        string $firstName,
        string $lastName,
        array $roles,
        ?string $tenantId = null,
        ?PhoneNumber $phoneNumber = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phoneNumber = $phoneNumber;
        $this->roles = $roles;
        $this->tenantId = $tenantId;
        $this->isActive = true;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

    public static function create(
        Email $email,
        HashedPassword $password,
        string $firstName,
        string $lastName,
        array $roles = [UserRole::CUSTOMER],
        ?string $tenantId = null,
        ?PhoneNumber $phoneNumber = null
    ): self {
        return new self(
            UserId::generate(),
            $email,
            $password,
            $firstName,
            $lastName,
            $roles,
            $tenantId,
            $phoneNumber
        );
    }

    public static function restore(
        UserId $id,
        Email $email,
        HashedPassword $password,
        string $firstName,
        string $lastName,
        array $roles,
        bool $isActive,
        ?string $tenantId,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
        ?PhoneNumber $phoneNumber = null
    ): self {
        $user = new self($id, $email, $password, $firstName, $lastName, $roles, $tenantId, $phoneNumber);
        $user->isActive = $isActive;
        $user->createdAt = $createdAt;
        $user->updatedAt = $updatedAt;

        return $user;
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    public function phoneNumber(): ?PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function fullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function tenantId(): ?string
    {
        return $this->tenantId;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return array<UserRole>
     */
    public function userRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(UserRole $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::ADMIN);
    }

    public function isManager(): bool
    {
        return $this->hasRole(UserRole::MANAGER);
    }

    public function changeEmail(Email $newEmail): void
    {
        $this->email = $newEmail;
        $this->touch();
    }

    public function changePassword(HashedPassword $newPassword): void
    {
        $this->password = $newPassword;
        $this->touch();
    }

    public function updateProfile(string $firstName, string $lastName): void
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->touch();
    }

    public function changePhoneNumber(?PhoneNumber $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
        $this->touch();
    }

    /**
     * @param array<UserRole> $roles
     */
    public function assignRoles(array $roles): void
    {
        $this->roles = $roles;
        $this->touch();
    }

    public function addRole(UserRole $role): void
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
            $this->touch();
        }
    }

    public function removeRole(UserRole $role): void
    {
        $this->roles = array_filter(
            $this->roles,
            fn (UserRole $r) => $r !== $role
        );
        $this->touch();
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->touch();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->touch();
    }

    public function assignToTenant(string $tenantId): void
    {
        $this->tenantId = $tenantId;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    // ---- UserInterface implementation ----

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        $roles = array_map(
            fn (UserRole $role) => $role->value,
            $this->roles
        );

        // Guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getPassword(): string
    {
        return $this->password->value();
    }

    public function getUserIdentifier(): string
    {
        return $this->email->value();
    }

    public function eraseCredentials(): void
    {
        // Clear any temporary, sensitive data
    }
}
