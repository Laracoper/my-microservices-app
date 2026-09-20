FROM php:8.4-fpm

# Аргументы для прав пользователя WSL Ubuntu (UID/GID 1000)
ARG UID=1000
ARG GID=1000

# 1. Установка системных утилит и зависимостей 
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

# 2. Компиляция и железобетонная установка расширений PHP (Redis и PDO MySQL)
RUN pecl channel-update pecl.php.net \
    && pecl install --configureoptions='enable-redis-igbinary="no" enable-redis-lzf="no" enable-redis-zstd="no"' redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 3. Магия Docker вашего друга: забираем готовую Node.js v22 и NPM
COPY --from=node:22 /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22 /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

# 4. Скачиваем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Настройка прав пользователя под вашу WSL Ubuntu
RUN getent group $GID || groupadd -g $GID laravel && \
    useradd -u $UID -g $GID -m -s /bin/bash laravel

WORKDIR /var/www/laravel-app

USER laravel
