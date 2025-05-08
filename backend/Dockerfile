FROM composer:2.5 AS build

WORKDIR /app

COPY /backend/composer.json .

RUN composer install --no-dev --no-scripts --ignore-platform-req=ext-sockets

COPY /backend .

RUN composer dumpautoload --optimize

FROM php:8.1-cli-alpine

WORKDIR /app

COPY --from=build /app .

RUN docker-php-ext-install pdo pdo_mysql mysqli sockets

RUN docker-php-ext-enable sockets

RUN chown -R www-data:www-data .

EXPOSE 5000

CMD [ "php", "-S", "0.0.0.0:5000", "-t", "/app/public", "-c", "php.ini", "-q" ]
