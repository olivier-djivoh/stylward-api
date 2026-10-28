#!/usr/bin/env bash

echo "Installation des dépendances Composer..."
composer install --no-dev --working-dir=/var/www/html

echo "Mise en cache de la configuration..."
php artisan config:cache

echo "Mise en cache des routes..."
php artisan route:cache

echo "Exécution des migrations..."
php artisan migrate --force
