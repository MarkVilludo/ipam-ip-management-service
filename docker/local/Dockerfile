# Used for prod build.
FROM php:8.2-fpm as php

# Install dependencies.
RUN apt-get update && apt-get install -y unzip libpq-dev libcurl4-gnutls-dev nginx libonig-dev

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_current.x | bash - && \
    apt-get update && \
    apt-get install -y nodejs && \
    npm --version && \
    node --version

# Install PHP extensions.
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libmcrypt-dev \
    libmagickwand-dev \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql bcmath curl opcache mbstring zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Copy composer executable.
COPY --from=composer:2.3.5 /usr/bin/composer /usr/bin/composer

# Copy configuration files.
COPY ./docker/local/php/php.ini /usr/local/etc/php/php.ini
COPY ./docker/local/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# For handling of queues
# RUN npm install pm2 -g

# Set working directory to /var/www.
WORKDIR /var/www

# Copy files from current folder to container current folder (set in workdir).
COPY --chown=www-data:www-data ./site .

# Create laravel caching folders.
RUN mkdir -p /var/www/storage/framework
RUN mkdir -p /var/www/storage/framework/cache
RUN mkdir -p /var/www/storage/framework/testing
RUN mkdir -p /var/www/storage/framework/sessions
RUN mkdir -p /var/www/storage/framework/views

# Fix files ownership.
RUN chown -R www-data /var/www/storage
RUN chown -R www-data /var/www/storage/framework
RUN chown -R www-data /var/www/storage/framework/sessions

# Set correct permission.
RUN chmod -R 755 /var/www/storage
RUN chmod -R 755 /var/www/storage/logs
RUN chmod -R 755 /var/www/storage/framework
RUN chmod -R 755 /var/www/storage/framework/sessions
RUN chmod -R 755 /var/www/bootstrap

# Adjust user permission & group
RUN usermod --uid 1000 www-data
RUN groupmod --gid 1001 www-data

CMD composer install --no-progress --no-interaction && \
    php-fpm -D && \
    nginx -g "daemon off;"
