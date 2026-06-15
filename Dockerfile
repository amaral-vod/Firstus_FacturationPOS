FROM php:8.3-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN composer dump-autoload --optimize \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x scripts/railway-start.sh

ENV PORT=8080
EXPOSE 8080

CMD ["scripts/railway-start.sh"]
