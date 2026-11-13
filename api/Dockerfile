# Imagem de produção (mantida separada, como exigido no projeto).
FROM dunglas/frankenphp:1-php8.4

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN install-php-extensions pdo_mysql zip bcmath opcache \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /app
COPY . /app
COPY Caddyfile /etc/frankenphp/Caddyfile
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader \
    && composer check-platform-reqs --no-dev \
    && mkdir -p storage/logs bootstrap/cache

EXPOSE 80
CMD ["sh", "-c", "if [ ! -e public/storage ] && [ ! -L public/storage ]; then php artisan storage:link; fi; exec frankenphp run --config /etc/frankenphp/Caddyfile"]
