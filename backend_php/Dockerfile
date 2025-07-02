# backend_php/Dockerfile
FROM php:8.2-apache
WORKDIR /var/www/html
COPY . .
RUN docker-php-ext-install pdo pdo_mysql
EXPOSE 80
# O CMD já é definido pela imagem php:8.2-apache para iniciar o servidor web