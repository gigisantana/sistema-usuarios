# backend_php/Dockerfile
FROM php:8.2-apache
WORKDIR /var/www/html
COPY . .

RUN apt-get update && \
    apt-get install -y \
        unzip \
        git \
        gnupg \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
        build-essential \
        locales \
        locales-all \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        zip \
        openssl \
        intl \
        mbstring \ 
        xml \     
    && docker-php-source delete

RUN composer install --no-dev --optimize-autoloader --no-scripts

EXPOSE 80
