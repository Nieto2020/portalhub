FROM php:8.3-fpm

# Dependencias necesarias
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli zip

# Directorio de trabajo
WORKDIR /var/www

# Configuración de zona horaria
ENV TZ=America/Mexico_City

CMD ["php-fpm"]