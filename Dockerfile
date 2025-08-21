FROM php:8.2-apache

# Install PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copy kode PHP ke container
COPY ./src /var/www/html/

# Set permission (opsional)
RUN chown -R www-data:www-data /var/www/html
