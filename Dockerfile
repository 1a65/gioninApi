FROM php:8.4-cli-alpine

RUN apk add --no-cache \
    curl-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-install \
    curl \
    mbstring \
    intl

COPY --from=composer:2 /usr/local/bin/composer /usr/local/bin/composer

WORKDIR /app

COPY composer.json ./

RUN composer install --no-interaction --prefer-dist --no-scripts

COPY . .

RUN composer dump-autoload --optimize

CMD ["php", "-a"]
