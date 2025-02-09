FROM php:8.3-rc-fpm-buster

# Copier le fichier de configuration PHP
COPY php.ini /usr/local/etc/php/conf.d/docker-php-config.ini

# Mettre à jour et installer les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    gnupg \
    g++ \
    procps \
    openssl \
    git \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    libicu-dev \
    libonig-dev \
    libxslt1-dev \
    libpq-dev \
    acl \
    zsh

# Configurer et installer les extensions PHP nécessaires
RUN docker-php-ext-configure gd --with-jpeg --with-freetype

RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    opcache \
    intl \
    zip \
    calendar \
    dom \
    mbstring \
    gd \
    xsl

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Définir le répertoire de travail
WORKDIR /var/www/minimaxi
