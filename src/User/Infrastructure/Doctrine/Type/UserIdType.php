<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Doctrine\Type;

use App\User\Domain\ValueObject\UserId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * Custom Doctrine type for UserId value object.
 */
final class UserIdType extends Type
{
    public const string NAME = 'user_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 36]);
    }

    /**
     * @throws ConversionException
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?UserId
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof UserId) {
            return $value;
        }

        if (!is_string($value)) {
            throw new ConversionException(sprintf(
                'Expected string or %s, got %s',
                UserId::class,
                get_debug_type($value)
            ));
        }

        try {
            return UserId::fromString($value);
        } catch (\InvalidArgumentException $e) {
            throw new ConversionException(sprintf(
                'Could not convert database value "%s" to UserId: %s',
                $value,
                $e->getMessage()
            ));
        }
    }

    /**
     * @throws ConversionException
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof UserId) {
            return $value->value();
        }

        throw new ConversionException(sprintf(
            'Expected %s or null, got %s',
            UserId::class,
            get_debug_type($value)
        ));
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
