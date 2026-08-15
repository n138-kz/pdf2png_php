FROM php:8.5-fpm

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN apt-get update && apt-get install -y \
    libmagickwand-dev \
    --no-install-recommends \
    pkg-config \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN pecl install imagick && docker-php-ext-enable imagick

WORKDIR /var/www/html
