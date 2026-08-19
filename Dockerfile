FROM php:8.2-apache

# Aktifkan ekstensi database MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Salin semua file project ke web directory Apache
COPY . /var/www/html/

# Atur izin akses folder
RUN chown -R www-data:www-data /var/www/html

# Expose port Apache
EXPOSE 80