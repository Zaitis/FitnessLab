# Production image: PHP-FPM only. The host's nginx (outside this
# container — see deploy/nginx/) terminates TLS and talks FastCGI directly
# to the port this image exposes; there is no web server in here.

FROM composer:2 AS vendor

# Composer checks installed packages' platform requirements (ext-bcmath,
# ext-intl) against this image's own PHP, not the runtime stage's — so it
# needs the same extensions installed here or the lock file "looks"
# incompatible even though the actual runtime has them.
RUN apk add --no-cache icu-dev \
    && docker-php-ext-install bcmath intl

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

FROM php:8.4-fpm-alpine

# Runtime shared libraries (libpq, libicu) stay installed permanently;
# only the -dev header/build packages get removed afterward. Deleting
# postgresql-dev/icu-dev directly (without --virtual) took libpq and
# libicu down with them as orphaned dependencies, breaking pdo_pgsql and
# intl at runtime with "unable to load dynamic library" — apk prunes
# transitively unless the build-only packages are isolated in their own
# virtual group first.
RUN apk add --no-cache postgresql-libs icu-libs \
    && apk add --no-cache --virtual .build-deps postgresql-dev icu-dev \
    && docker-php-ext-install pdo_pgsql bcmath intl opcache \
    && apk del .build-deps

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

WORKDIR /var/www/html

COPY --from=vendor /app .

RUN chown -R www-data:www-data storage bootstrap/cache

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
