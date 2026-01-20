<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Doctrine\Type;

use App\User\Domain\ValueObject\HashedPassword;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * Custom Doctrine type for HashedPassword value object.
 */
final class HashedPasswordType extends Type
{
    public const string NAME = 'hashed_password';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 255]);
    }

    /**
     * @throws ConversionException
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?HashedPassword
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof HashedPassword) {
            return $value;
        }

        if (!is_string($value)) {
            throw new ConversionException(sprintf(
                'Expected string or %s, got %s',
                HashedPassword::class,
                get_debug_type($value)
            ));
        }

        try {
            return HashedPassword::fromHash($value);
        } catch (\InvalidArgumentException $e) {
            throw new ConversionException(sprintf(
                'Could not convert database value to HashedPassword: %s',
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

        if ($value instanceof HashedPassword) {
            return $value->value();
        }

        throw new ConversionException(sprintf(
            'Expected %s or null, got %s',
            HashedPassword::class,
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
