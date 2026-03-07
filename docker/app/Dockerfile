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

# Install PHP dependencies.
# --no-scripts  — skips post-autoload-dump (php artisan package:discover) which
#                 requires a bootstrapped Laravel app (.env + APP_KEY) that does
#                 not exist at image build time.
# COMPOSER_MEMORY_LIMIT=-1 — prevents OOM kills on memory-constrained builders.
# dump-autoload is run separately to produce the optimised classmap.
RUN COMPOSER_MEMORY_LIMIT=-1 composer install \
        --no-interaction \
        --prefer-dist \
        --no-dev \
        --no-scripts \
    && composer dump-autoload --optimize --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

COPY docker/app/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
