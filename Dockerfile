FROM serversideup/php:8.3-fpm-nginx

# Active les automatisations Laravel (migrations, cache config, etc.)
ENV AUTORUN_ENABLED=true
ENV AUTORUN_LARAVEL_MIGRATION=true
ENV AUTORUN_LARAVEL_CONFIG_CACHE=true

# Copie du code (l'image se charge du reste)
COPY . /var/www/html

# (Optionnel) Si tu veux garder ton script de déploiement
# COPY scripts/ /etc/entrypoint.d/
