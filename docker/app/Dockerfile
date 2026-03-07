FROM php:8.3-fpm-alpine

# Install system dependencies + build tools required by PECL
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    icu-dev \
    linux-headers \
    autoconf \
    g++ \
    make

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        intl \
        bcmath \
        pcntl \
        opcache

# Install Redis extension via PECL, then remove build tools to keep image small
RUN pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies (production-optimised; override in dev with volume mount)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
