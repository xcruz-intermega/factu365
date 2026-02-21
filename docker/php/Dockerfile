FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libxml2-dev \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    $PHPIZE_DEPS

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        xml \
        bcmath \
        intl \
        gd \
        soap \
        zip \
        opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | sh 2>/dev/null || true
RUN apk add --no-cache nodejs npm

WORKDIR /var/www/html

RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www
USER www

EXPOSE 9000
CMD ["php-fpm"]
