FROM php:8.5-fpm

RUN apt-get update && apt-get install -y \
  git unzip libpq-dev zip \
  && docker-php-ext-install pdo_pgsql \
  && pecl install xdebug redis \
  && docker-php-ext-enable xdebug redis

# composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Fix git ownership issue for mounted volumes
RUN git config --global --add safe.directory /app \
  && git config --global --add safe.directory '*'

# Set ownership (optional, for production tighten permissions)
# RUN chown -R www-data:www-data /app
# Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]


