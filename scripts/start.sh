#!/bin/sh
# ─────────────────────────────────────────────────────────────────────────────
# Container startup script for EngineeringAgent (Laravel 12)
#
# Order of operations:
#   1. Wait for the database to be reachable (avoids race condition on boot)
#   2. Run migrations  (--force bypasses the production prompt)
#   3. Run seeders     (idempotent — safe to run on every startup)
#   4. Start Laravel's built-in server on 0.0.0.0:$PORT
# ─────────────────────────────────────────────────────────────────────────────

set -e

cd /var/www/html

echo "==> Clearing cached config (ensures fresh env vars are picked up)..."
php artisan config:clear

echo "==> Caching config for production performance..."
php artisan config:cache

echo "==> Caching routes..."
php artisan route:cache

echo "==> Caching views..."
php artisan view:cache

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Running database seeders..."
php artisan db:seed --force

echo "==> Creating storage symlink (if not exists)..."
php artisan storage:link --force 2>/dev/null || true

# Use $PORT if set by Render, otherwise default to 8000
PORT="${PORT:-8000}"

echo "==> Starting Laravel server on 0.0.0.0:${PORT}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
