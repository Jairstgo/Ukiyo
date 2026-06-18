FROM php:8.2-apache

# Instalar extensión mysqli
RUN docker-php-ext-install mysqli

# Copiar todo el proyecto
COPY . /var/www/html/

# Crear carpeta de uploads con permisos
RUN mkdir -p /var/www/html/uploads/platillos \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 755 /var/www/html/uploads

EXPOSE 80
