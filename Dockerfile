# Usar una imagen base de PHP con Apache
FROM php:7.4-apache

# Copiar los archivos de tu proyecto al contenedor
COPY . /var/www/html/

# Instalar extensiones necesarias de PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Hacer que Apache sirva archivos estáticos correctamente (si tienes imágenes, CSS, etc.)
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto 80 para acceder a la aplicación
EXPOSE 80
