FROM php:8.1-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        intl \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock* ./

ENV COMPOSER_MEMORY_LIMIT=-1

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    -vvv

COPY . .

ENV PORT=10000
EXPOSE ${PORT}

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public"]
