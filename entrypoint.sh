#!/bin/bash
set -e

# Configuration du port d'écoute (dynamique pour Koyeb, Render, Railway, etc.)
PORT="${PORT:-80}"
sed -i "s/listen [0-9]\+/listen ${PORT}/g" /etc/nginx/sites-available/default
sed -i "s/listen \[::\]:[0-9]\+/listen [::]:${PORT}/g" /etc/nginx/sites-available/default

# Permissions et dossiers storage / cache
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Création du lien symbolique de stockage si absent
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link || true
fi

# Exécution des migrations de base de données (si configurée)
if [ -n "$DB_HOST" ] || [ -n "$DB_URL" ]; then
    echo "Exécution des migrations de base de données..."
    php artisan migrate --force || echo "Avertissement: Les migrations ont rencontré un problème ou la base n'est pas encore joignable."
fi

# Optimisations Laravel en production
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "Démarrage de Nginx et PHP-FPM sur le port ${PORT}..."
service nginx start
exec php-fpm -F