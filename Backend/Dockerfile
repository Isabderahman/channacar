# Laravel API (PHP-FPM). Code is bind-mounted by docker-compose; this image
# only provides the PHP runtime, extensions and Composer.
FROM php:8.2-fpm-alpine

# Runtime + build dependencies, then PHP extensions (gd is needed by dompdf
# for the rental-contract PDF, pdo_mysql for the database, etc.).
RUN apk add --no-cache \
        bash git curl icu-libs oniguruma libzip libpng libjpeg-turbo freetype \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS icu-dev oniguruma-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring bcmath gd zip exif intl opcache \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
