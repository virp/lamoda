FROM composer AS composer
COPY . ./
RUN composer install -n

FROM node:10 AS npm
WORKDIR /app/
COPY . ./
RUN npm install && npm run prod

FROM php:7.3-apache
WORKDIR /app/
ARG CONTAINER_CAPACITY=10
ARG SEED_PRODUCTS_COUNT=100
ARG SEED_CONTAINERS_COUNT=1000
ENV DB_CONNECTION=sqlite \
    CONTAINER_CAPACITY=$CONTAINER_CAPACITY \
    SEED_PRODUCTS_COUNT=$SEED_PRODUCTS_COUNT \
    SEED_CONTAINERS_COUNT=$SEED_CONTAINERS_COUNT \
    APACHE_DOCUMENT_ROOT=/app/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
COPY --chown=www-data:www-data . /app/
COPY --chown=www-data:www-data --from=composer /app/vendor /app/vendor
COPY --chown=www-data:www-data --from=npm /app/public /app/public
RUN /app/vendor/bin/phpunit
RUN touch /app/.env \
    && echo 'APP_KEY=' >> /app/.env \
    && touch /app/database/database.sqlite \
    && chown www-data:www-data /app/database/database.sqlite \
    && php artisan key:generate \
    && php artisan migrate --force --seed \
    && a2enmod rewrite
