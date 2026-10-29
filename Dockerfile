FROM serversideup/php:8.3-fpm-nginx

# On passe en root pour installer les paquets
USER root

# 1. On copie le code
COPY --chown=www-data:www-data . /var/www/html

# 2. On installe les dépendances Composer
RUN composer install --no-dev --optimize-autoloader

# 3. 👇 TRÈS IMPORTANT : On installe l'extension GD pour les images
RUN install-php-extensions gd

# 4. 👇 On s'assure que les dossiers existent et ont les bonnes permissions
RUN mkdir -p /var/www/html/storage/app/public/shops /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views /var/www/html/storage/logs
RUN chown -R www-data:www-data /var/www/html/storage
RUN chmod -R 775 /var/www/html/storage

# On remet l'utilisateur sécurisé
USER www-data

# Configuration des automatisations Laravel
ENV AUTORUN_ENABLED=true
ENV AUTORUN_LARAVEL_MIGRATION=true
ENV AUTORUN_LARAVEL_CONFIG_CACHE=true