#!/usr/bin/env bash
# Corrige l'accès MySQL (erreur 500 / Access denied firstus@localhost)
set -euo pipefail

cd "$(dirname "$0")/.."

echo "==> Configuration MySQL (sudo requis)..."
sudo mysql < database/setup_mysql.sql

echo "==> Test connexion..."
mysql -u firstus -pfirstus123 -e "SELECT 'MySQL OK' AS status;"

echo "==> Migrations..."
php artisan migrate --force

echo "==> Seeders (si base vide)..."
php artisan db:seed --force 2>/dev/null || true

echo "==> Test HTTP login..."
curl -s -o /dev/null -w "HTTP %{http_code}\n" http://127.0.0.1:8000/login || echo "(démarrez ./scripts/dev-local.sh si le serveur est arrêté)"

echo "==> Terminé."
