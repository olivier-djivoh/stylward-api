FROM serversideup/php:8.3-fpm-nginx

# On passe en root pour pouvoir installer les dépendances
USER root

# On copie le code et on donne les droits au serveur web
COPY --chown=www-data:www-data . /var/www/html

# On installe les dépendances Composer dans l'image
RUN composer install --no-dev --optimize-autoloader

# On remet l'utilisateur par défaut (www-data) pour la sécurité
USER www-data

# Configuration des automatisations Laravel
ENV AUTORUN_ENABLED=true
ENV AUTORUN_LARAVEL_MIGRATION=true
ENV AUTORUN_LARAVEL_CONFIG_CACHE=true

RUN php artisan config:clear

RUN php -m | grep pdo_pgsql
