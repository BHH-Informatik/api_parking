# Erstelle einen neuen composer Container
FROM composer:latest AS build
# Setze die Umgebungsvariable COMPOSER_ALLOW_SUPERUSER auf 1
ENV COMPOSER_ALLOW_SUPERUSER 1
# Setze das Arbeitsverzeichnis auf /app
WORKDIR /app
# Kopiere den php-extension-installer in den Container
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
# Kopiere das backend in den Container
# COPY ./ /app
COPY composer.json composer.lock /app/
# Update die Abhängigkeiten
RUN composer update
# Installiere die Abhängigkeiten
RUN cd /app && apk add --no-cache libpng libpng-dev \
    && docker-php-ext-install gd
RUN composer install --optimize-autoloader \
        --no-progress \
        --quiet \
        --no-interaction

# Erstelle einen php apache Container mit der neusten Version
FROM php:apache-bullseye AS production
# Setze die Zeitzone auf UTC
ENV TZ="UTC"
# Setze das Arbeitsverzeichnis auf /app
WORKDIR /app
# Kopiere die backend datein in den Container
COPY ./ /app
# Kopiere die vendor datein aus der Build stage in den Container
COPY --from=build /app/vendor /app/vendor

# if vendor is missing, throw error
RUN if [ ! -d /app/vendor ]; then echo "Vendor is missing"; exit 1; fi

# Update den Container, konfiguriere die php extensions und installiere die Abhängigkeiten. Danach setze den Besitzer auf www-data und setze die apache config
RUN apt-get update -y && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zlib1g-dev libicu-dev g++ \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql intl \
    && chown -R www-data:www-data /app \
    && echo '<VirtualHost *:80>\n DocumentRoot /app/public\n <Directory /app/public>\n AllowOverride All\n Require all granted\n </Directory>\n</VirtualHost>' > /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite
# Setze den default user auf www-data
USER www-data
