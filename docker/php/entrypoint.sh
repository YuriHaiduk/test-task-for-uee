#!/bin/sh
set -e

cd /var/www/backend

# 1. Install PHP dependencies on first boot (vendor is git-ignored).
if [ ! -d vendor ]; then
    echo "==> Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --no-progress
fi

# 2. Ensure an environment file exists.
if [ ! -f .env ]; then
    echo "==> Creating .env from .env.example..."
    cp .env.example .env
fi

# 3. Generate the application key only if it is not set yet.
if ! grep -q '^APP_KEY=base64:' .env; then
    echo "==> Generating application key..."
    php artisan key:generate --force
fi

# 4. Wait for PostgreSQL to accept connections.
echo "==> Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT}..."
until pg_isready -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USERNAME}" >/dev/null 2>&1; do
    sleep 1
done

# 5. Run migrations (already-applied migrations are skipped).
echo "==> Running migrations..."
php artisan migrate --force

# 6. Seed only on first boot, i.e. when no companies exist yet. This keeps
#    restarts clean instead of appending example rows on every `up`.
COMPANIES=$(PGPASSWORD="${DB_PASSWORD}" psql -h "${DB_HOST}" -p "${DB_PORT}" \
    -U "${DB_USERNAME}" -d "${DB_DATABASE}" -tAc 'SELECT COUNT(*) FROM companies' 2>/dev/null || echo 0)
if [ "${COMPANIES}" = "0" ]; then
    echo "==> Seeding database..."
    php artisan db:seed --force
else
    echo "==> Companies already present, skipping seed."
fi

# 7. Make Laravel's writable directories writable (defensive on bind mounts).
chmod -R 775 storage bootstrap/cache || true

echo "==> Bootstrap complete, starting: $*"
exec "$@"
