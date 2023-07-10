# Base image
FROM php:7.4-apache

# Set the working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath opcache intl

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the application code
COPY . .

# Install application dependencies
RUN composer install --no-dev --optimize-autoloader

# Set the required permissions
RUN chown -R www-data:www-data var

# Expose port 80
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
