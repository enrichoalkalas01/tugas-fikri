# Use PHP with Apache as base image
FROM php:8.2-apache

# Demo persistence uses SQLite; no separate MySQL container is needed.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev libonig-dev \
    && docker-php-ext-install pdo_sqlite mbstring

# Enable Apache mod_rewrite (useful for routing)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy all project files to the container
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
