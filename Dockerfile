
# IMAGEN BASE
# Usamos una versión específica y muy moderna de PHP (8.3.12) que incluye Apache.
# "bookworm" indica que el sistema operativo base es Debian 12 (muy estable).
FROM php:8.3.12-apache-bookworm

# ACTUALIZACIÓN E INSTALACIÓN DE HERRAMIENTAS DEL SISTEMA Y EXTENSIONES PHP
# Esto descarga librerías del sistema operativo (como soporte para imágenes, zip, git, curl) 
# y luego instala y configura todas las extensiones de PHP que Laravel exige para funcionar.
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
# CONFIGURACIÓN DE APACHE PARA LARAVEL
# Laravel usa "URLs amigables" (ej: reparaya.com/usuarios en lugar de index.php?page=usuarios). 
# Habilitamos el módulo 'rewrite' para que Apache pueda leer y redirigir estas rutas correctamente.
RUN a2enmod rewrite

# Permitir .htaccess en el directorio web
# PERMISOS DE SOBRESCRITURA (.htaccess)
# Laravel incluye un archivo de configuración llamado .htaccess. 
# Esta línea modifica el archivo principal de Apache para darle permiso a Laravel de aplicar sus propias reglas de seguridad y enrutamiento.
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# DIRECTORIO DE TRABAJO
# Establece la carpeta predeterminada dentro del contenedor donde se ejecutarán los siguientes comandos y donde vivirá el proyecto.
WORKDIR /var/www/html
WORKDIR /var/www/html

# COPIA DE ARCHIVOS
# Copia todo el código local hacia dentro del contenedor.
COPY . /var/www/html