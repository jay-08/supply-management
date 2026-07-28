FROM richarvey/nginx-php-fpm:latest

# Copy application files
COPY . /var/www/html

# Set Laravel public folder as web root
ENV DOCUMENT_ROOT=/var/www/html/public
ENV INDEX_FILE=index.php
ENV PHP_MEM_LIMIT=512M
ENV ROUTING=laravel

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions for storage & cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
