FROM php:8.3-fpm-alpine

# Install system deps
RUN apk add --no-cache git unzip libzip-dev libpng-dev oniguruma-dev openssl postgresql-client postgresql-dev build-base

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring zip

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create app dir
WORKDIR /app

COPY composer.json composer.lock /app/
RUN composer install --no-interaction --prefer-dist --no-scripts --no-progress --no-plugins || true

COPY . /app

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
