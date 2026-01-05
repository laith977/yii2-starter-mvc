FROM php:8.2-apache

# PHP & Apache Configuration
# Installs required PHP extensions for Yii2:
# - pdo_mysql: Database connectivity
# - mbstring: String handling (required by Yii2)
# - intl: Internationalization

# Enable Apache mod_rewrite for URL routing
RUN a2enmod rewrite

# Install system dependencies required for PHP extensions
RUN apt-get update && apt-get install -y \
    default-libmysqlclient-dev \
    libonig-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    intl

# Set working directory
WORKDIR /var/www/html

# Copy Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
