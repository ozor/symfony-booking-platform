<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

/**
 * Enum representing user roles in the system.
 */
enum UserRole: string
{
    case ADMIN = 'ROLE_ADMIN';
    case MANAGER = 'ROLE_MANAGER';
    case STAFF = 'ROLE_STAFF';
    case CUSTOMER = 'ROLE_CUSTOMER';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Администратор',
            self::MANAGER => 'Менеджер',
            self::STAFF => 'Сотрудник',
            self::CUSTOMER => 'Клиент',
        };
    }

    public function isPrivileged(): bool
    {
        return in_array($this, [self::ADMIN, self::MANAGER], true);
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
