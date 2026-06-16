#!/usr/bin/env bash
# Développement LOCAL uniquement (127.0.0.1 — pas d'exposition réseau)
set -euo pipefail

cd "$(dirname "$0")/.."

echo "🛒 Firstus POS — mode LOCAL"
echo ""
echo "   Connexion    : http://127.0.0.1:8000/login"
echo "   Dashboard    : http://127.0.0.1:8000/dashboard"
echo "   POS          : http://127.0.0.1:8000/caisse"
echo "   Proforma     : http://127.0.0.1:8000/facturation/proforma/nouvelle"
echo ""
echo "   Base MySQL   : firstus_pos @ 127.0.0.1:3306"
echo "   Doc liens    : docs/LIENS.md"
echo "   Git push     : ./scripts/push-remotes.sh"
echo ""

if ! php artisan db:show 2>/dev/null | grep -q mysql; then
    echo "⚠️  MySQL — si besoin : sudo mysql < database/setup_mysql.sql"
    echo ""
fi

php artisan serve --host=127.0.0.1 --port=8000
