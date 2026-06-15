#!/usr/bin/env bash
# Crée la base MySQL et l'utilisateur Firstus POS
set -euo pipefail

cd "$(dirname "$0")/.."

echo "==> Création base MySQL firstus_pos..."
if command -v sudo >/dev/null 2>&1; then
    sudo mysql < database/setup_mysql.sql
else
    mysql -u root -p < database/setup_mysql.sql
fi

echo "==> Migrations + seeders..."
php artisan migrate:fresh --force --seed

echo "==> Terminé. Vérifiez .env : DB_CONNECTION=mysql"
