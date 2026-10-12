FROM php:8.3.35-fpm-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    curl \
    git \
    libcurl4-openssl-dev \
    libicu-dev \
    libonig-dev \
    libsqlite3-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install -j"$(nproc)" \
    bcmath \
    curl \
    dom \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_mysql \
    pdo_sqlite \
    xml \
    zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

COPY docker/php/local.ini /usr/local/etc/php/conf.d/99-cim-local.ini
COPY docker/php/entrypoint.sh /usr/local/bin/cim-entrypoint

RUN chmod +x /usr/local/bin/cim-entrypoint

WORKDIR /var/www/html

ENTRYPOINT ["cim-entrypoint"]

CMD ["php-fpm", "-F"]