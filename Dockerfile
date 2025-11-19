FROM php:8.1-apache

# Metadata
LABEL maintainer="DigiParc Team <support@digiparc.com>"
LABEL version="3.0.0"
LABEL description="DigiParc Fleet Management System - Enterprise Edition"

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    cron \
    mysql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache modules
RUN a2enmod rewrite headers ssl

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create required directories
RUN mkdir -p \
    public/uploads/logos \
    public/uploads/documents \
    public/uploads/vehicles \
    public/uploads/drivers \
    storage/logs \
    storage/cache \
    storage/backups \
    var/log

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 public/uploads storage var/log

# Configure Apache
COPY config/apache/digiparc.conf /etc/apache2/sites-available/000-default.conf

# PHP Configuration
RUN echo "upload_max_filesize = 10M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 12M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "expose_php = Off" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "display_errors = Off" >> /usr/local/etc/php/conf.d/security.ini

# Setup CRON jobs
RUN echo "0 * * * * php /var/www/html/app/cron/check_alerts.php >> /var/www/html/var/log/cron-alerts.log 2>&1" | crontab - \
    && echo "0 2 * * * php /var/www/html/app/cron/daily_backup.php >> /var/www/html/var/log/cron-backup.log 2>&1" | crontab -

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose ports
EXPOSE 80 443

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start services
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
