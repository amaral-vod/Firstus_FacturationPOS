#!/usr/bin/env bash
# À exécuter SUR le serveur Hostinger après upload du code
# cd /home/u648457710/domains/easygest.org/public_html/facturation
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> Firstus POS — déploiement easygest.org"
echo "    Racine: $ROOT"

if [ ! -f .env ]; then
    cp .env.production.easygest.example .env
    echo "⚠️  Fichier .env créé — éditez DB_* et APP_KEY avant de continuer."
    exit 1
fi

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force
fi

echo "==> Composer..."
if command -v composer >/dev/null 2>&1; then
    composer install --no-dev --optimize-autoloader --no-interaction
else
    echo "⚠️  Composer absent — installez les vendor/ en local puis uploadez."
fi

echo "==> Permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "==> Migrations..."
php artisan migrate --force

echo "==> Lien storage..."
php artisan storage:link 2>/dev/null || true

echo "==> Cache production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "✅ Terminé."
echo "   URL : https://facturation.easygest.org/login"
echo "   Vérifiez que la racine web pointe vers : $ROOT/public"
