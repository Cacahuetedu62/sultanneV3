FROM php:8.2-apache
# Mise à jour et installation des dépendances
RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    default-mysql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli zip

# Installation de l'extension MongoDB
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration Apache
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Création d'un utilisateur non-root
RUN groupadd -r appuser && useradd -r -g appuser appuser

WORKDIR /var/www/html

# Copie des fichiers
COPY --chown=appuser:appuser . /var/www/html/

# Installation des dépendances
RUN composer install --no-dev --no-interaction \
    && chown -R appuser:appuser /var/www/html

# Changement d'utilisateur
USER appuser

EXPOSE 80

# Commande par défaut
CMD ["apache2-foreground"]