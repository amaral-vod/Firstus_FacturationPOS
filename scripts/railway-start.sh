#!/usr/bin/env bash
set -euo pipefail

cd /app

echo "==> Firstus POS — démarrage Railway"

if [ -z "${APP_KEY:-}" ]; then
    echo "ERREUR: définissez APP_KEY dans les variables Railway."
    echo "Générez une clé : php artisan key:generate --show"
    exit 1
fi

# Railway expose souvent DATABASE_URL ; Laravel attend DB_URL
if [ -n "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
    export DB_URL="${DATABASE_URL}"
fi

export DB_CONNECTION="${DB_CONNECTION:-pgsql}"

php artisan config:clear
php artisan migrate --force

if [ "${RUN_SEEDERS:-true}" = "true" ]; then
    php artisan db:seed --force || true
fi

php artisan storage:link 2>/dev/null || true

if [ "${APP_ENV:-local}" != "local" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

PORT="${PORT:-8080}"
echo "==> Écoute sur 0.0.0.0:${PORT}"
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
