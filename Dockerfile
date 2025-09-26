# Étape 1 : image PHP avec extensions nécessaires
FROM php:8.2-fpm

# Installer dépendances système et extensions PHP utiles pour Symfony
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install intl pdo pdo_mysql opcache zip

# Étape 2 : Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Étape 3 : Définir le répertoire de travail
WORKDIR /var/www/html

# Étape 4 : Copier les fichiers du projet
COPY . .

# Étape 5 : Installer les dépendances Symfony (sans les dev si en prod)
RUN composer install --no-scripts --no-interaction --prefer-dist

# Étape 6 : Droits d’écriture pour Symfony (cache, logs)
RUN chown -R www-data:www-data var

# Exposer le port PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
