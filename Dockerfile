# Pakai image PHP + Apache
FROM php:8.1-apache

# Copy semua file project ke folder apache (/var/www/html)
COPY . /var/www/html/

# Aktifkan mod_rewrite (kalau perlu .htaccess)
RUN a2enmod rewrite

# Buka port 80 untuk Railway
EXPOSE 80
