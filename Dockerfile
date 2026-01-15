# Use official PHP image with Apache 
FROM php:8.2-apache

# Copy project files to Apache document root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Enable Apache mod_rewrite (if needed)
RUN a2enmod rewrite

# Install PHP extensions and tools required by the app
RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev unzip \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Give write permissions to the web server (if your scripts create files)
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 inside the container
EXPOSE 80

# Copy custom PHP configuration
COPY custom-php.ini /usr/local/etc/php/conf.d/

# Install required extensions
RUN apt-get update && apt-get install -y \
    zip unzip libzip-dev dos2unix \
  && docker-php-ext-install zip

# Add entrypoint script to show message and start Apache
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# Convert line endings and set execution permission
RUN dos2unix /usr/local/bin/docker-entrypoint.sh \
  && chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
