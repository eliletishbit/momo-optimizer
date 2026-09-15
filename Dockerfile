FROM php:8.2-fpm

# Installer les dépendances système et bibliothèques requises
RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql pdo_pgsql pgsql zip bcmath pcntl opcache gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration Nginx optimisée pour Laravel
RUN echo 'server { \
    listen 80 default_server; \
    listen [::]:80 default_server; \
    index index.php index.html; \
    root /var/www/html/public; \
    client_max_body_size 20M; \
    location / { try_files $uri $uri/ /index.php?$query_string; } \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        include fastcgi_params; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        fastcgi_buffers 16 16k; \
        fastcgi_buffer_size 32k; \
    } \
    location ~* \.(js|css|woff2|woff|ttf|png|jpg|jpeg|gif|ico|svg)$ { \
        expires max; \
        log_not_found off; \
        access_log off; \
    } \
}' > /etc/nginx/sites-available/default

WORKDIR /var/www/html

# Dépendances Composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --prefer-dist

# Dépendances NPM
COPY package.json package-lock.json ./
RUN npm ci || npm install

# Copier le code source de l'application
COPY . .

# Build des assets Vite (TailwindCSS / Livewire / JS)
RUN npm run build

# Permissions des dossiers storage et bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copier le script d'entrée
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]