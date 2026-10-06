FROM php:8.3.12-apache-bookworm

# Instalar dependencias necesarias para la laravel y extensiones de PHP
RUN apt-get update && apt-get upgrade -y && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        mbstring \
        xml \
        bcmath \
        opcache

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Permitir .htaccess en el directorio web
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY . /var/www/html