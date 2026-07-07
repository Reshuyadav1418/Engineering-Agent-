#!/bin/sh
# ─────────────────────────────────────────────────────────────────────────────
# Container startup script for EngineeringAgent (Laravel 12)
#
# Order of operations:
#   1. Ensure writable storage directories exist
#   2. Cache config / routes / views
#   3. Run migrations  (--force bypasses the production prompt)
#   4. Start Laravel server immediately (so Render port scan succeeds)
#   5. Run seeders in background (idempotent — safe to run on every startup)
# ─────────────────────────────────────────────────────────────────────────────

set -e

cd /var/www/html

echo "==> Ensuring storage directories exist and are writable..."
mkdir -p storage/framework/sessions \
         storage/framework/views \
         storage/framework/cache/data \
         storage/logs \
         bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "==> Clearing cached config (ensures fresh env vars are picked up)..."
php artisan config:clear

echo "==> Caching config for production performance..."
php artisan config:cache

echo "==> Caching routes..."
php artisan route:cache

echo "==> Caching views..."
php artisan view:clear  2>/dev/null || true
php artisan view:cache  2>/dev/null || true

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

# Use $PORT if set by Render, otherwise default to 8000
PORT="${PORT:-8000}"

echo "==> Starting Laravel server on 0.0.0.0:${PORT}..."
echo "==> (Database seeding will run in the background after server starts)"

# Run seeder in background AFTER server has had a moment to bind the port
(sleep 5 && echo "==> Running database seeders in background..." && php artisan db:seed --force && echo "==> Seeding complete.") &

exec php artisan serve --host=0.0.0.0 --port="${PORT}"
