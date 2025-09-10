FROM php:8.3-fpm

# Instala dependências do sistema e extensões PHP essenciais
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
    zip unzip curl git libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
