#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
    # Install Composer dependencies if not present (fail-safe)
    if [ ! -d "vendor" ]; then
        composer install --prefer-dist --no-progress --no-interaction
    fi

    # Generate JWT keys if missing
    if [ ! -f config/jwt/private.pem ]; then
        echo "Generating JWT keys..."
        php bin/console lexik:jwt:generate-keypair --skip-if-exists
    fi

    # Wait for DB to be ready (double check, though depends_on helps)
    # Applying migrations for DEV
    echo "Applying migrations (DEV)..."
    php bin/console doctrine:migrations:migrate --no-interaction

    # Setting up TEST database
    echo "Setting up TEST database..."
    php bin/console doctrine:database:create --env=test --if-not-exists
    php bin/console doctrine:migrations:migrate --env=test --no-interaction
fi

exec docker-php-entrypoint "$@"
