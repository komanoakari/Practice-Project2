FROM php:8.2-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    default-mysql-client zlib1g-dev libzip-dev unzip git ca-certificates \
    && docker-php-ext-install pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www
