FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    bash \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    zlib1g-dev \
    curl \
    && docker-php-ext-install pdo pdo_mysql zip mbstring xml

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files
# COPY composer.json composer.lock* ./

# Install dependencies
# RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copy project
COPY . .

# Default command
# CMD ["php", "artisan", "mcp:serve"]
CMD ["bash"]

