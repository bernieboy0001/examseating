FROM php:8.2-apache

# Install MySQL PDO driver
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache rewrite
RUN a2enmod rewrite

# Copy project
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html
