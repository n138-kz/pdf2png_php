FROM php:8.5-fpm

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN apt-get update && apt-get install -y \
    libmagickwand-dev \
    --no-install-recommends \
    pkg-config \
    ghostscript \
    tree \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install zip


# policy.xml の PDF ブロックを解除するコマンドを RUN の最後などに追加
RUN sed -i 's/rights="none" pattern="PDF"/rights="read|write" pattern="PDF"/' /etc/ImageMagick-*/policy.xml

WORKDIR /var/www/html
