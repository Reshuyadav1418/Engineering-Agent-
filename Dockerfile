# ─── Stage 1: Build frontend assets (Node / Vite) ──────────────────────────
FROM node:20-alpine AS frontend

WORKDIR /app

# Copy only package files first for better layer caching
COPY package.json package-lock.json ./
RUN npm ci

# Copy source and build
COPY resources ./resources
COPY vite.config.js tailwind.config.js ./
COPY public ./public
RUN npm run build


# ─── Stage 2: PHP Application ──────────────────────────────────────────────
FROM php:8.3-cli AS app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    xml

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first (layer cache)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy application source
COPY . .

# Copy built frontend assets from stage 1
COPY --from=frontend /app/public/build ./public/build

# Ensure storage and cache directories exist and are writable
RUN mkdir -p storage/framework/{sessions,views,cache} \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Run post-install scripts (package:discover etc.)
RUN composer run-script post-autoload-dump || true

# Copy and set permissions on startup script
COPY scripts/start.sh /start.sh
RUN chmod +x /start.sh

# Expose the port Laravel will serve on
EXPOSE 8000

CMD ["/start.sh"]
