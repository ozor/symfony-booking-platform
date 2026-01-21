<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Doctrine\Type;

use App\User\Domain\ValueObject\Email;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

/**
 * Custom Doctrine type for Email value object.
 */
final class EmailType extends Type
{
    public const string NAME = 'email';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 255]);
    }

    /**
     * @throws ConversionException
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Email
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Email) {
            return $value;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException(
                sprintf('Expected string, got %s', get_debug_type($value))
            );
        }

        try {
            return Email::fromString($value);
        } catch (InvalidArgumentException $e) {
            throw new ConversionException(sprintf(
                'Could not convert database value "%s" to Email: %s',
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

        if ($value instanceof Email) {
            return $value->value();
        }

        if (is_string($value)) {
            return $value;
        }

        throw new ConversionException(sprintf(
            'Expected %s or null, got %s',
            Email::class,
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
