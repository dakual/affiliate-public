FROM composer:2.5 AS build

WORKDIR /app

COPY /fe-user/composer.json .

RUN composer install --no-dev --no-scripts

COPY /fe-user .

RUN composer dumpautoload --optimize

FROM php:8.1-fpm-alpine

WORKDIR /app

COPY --from=build /app .

RUN chown -R www-data:www-data .

EXPOSE 5000

CMD [ "php", "-S", "0.0.0.0:5000", "-t", "/app/public", "-c", "php.ini", "-q" ]
