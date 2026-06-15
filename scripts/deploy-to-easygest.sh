#!/usr/bin/env bash
# Déploiement automatique vers facturation.easygest.org (Hostinger)
# Usage : copiez deploy/easygest.env.example → deploy/easygest.env puis ./scripts/deploy-to-easygest.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$ROOT/deploy/easygest.env"

if [ ! -f "$ENV_FILE" ]; then
    echo "❌ Créez $ENV_FILE à partir de deploy/easygest.env.example"
    exit 1
fi
# shellcheck source=/dev/null
source "$ENV_FILE"

: "${SSH_USER:=u648457710}"
: "${SSH_PORT:=65002}"
: "${REMOTE_PATH:=/home/u648457710/domains/easygest.org/public_html/facturation}"
: "${SSH_HOST:?Définissez SSH_HOST dans deploy/easygest.env}"

SSH_OPTS=(-p "$SSH_PORT" -o StrictHostKeyChecking=accept-new)
[ -n "${SSH_KEY:-}" ] && SSH_OPTS+=(-i "$SSH_KEY")

REMOTE="${SSH_USER}@${SSH_HOST}"
RSYNC_SSH="ssh ${SSH_OPTS[*]}"

echo "==> Sync code → $REMOTE:$REMOTE_PATH"
rsync -avz --delete \
    --exclude '.git' \
    --exclude '.env' \
    --exclude 'node_modules' \
    --exclude 'storage/logs/*' \
    --exclude 'deploy/easygest.env' \
    --exclude 'deploy/firstus_pos.sql.gz' \
    -e "$RSYNC_SSH" \
    "$ROOT/" "$REMOTE:$REMOTE_PATH/"

echo "==> .env production"
ssh "${SSH_OPTS[@]}" "$REMOTE" bash -s <<REMOTE_SCRIPT
set -euo pipefail
cd "$REMOTE_PATH"
if [ ! -f .env ]; then
    cp .env.production.easygest.example .env
fi
REMOTE_SCRIPT

# Inject DB + APP_KEY si définis localement
if [ -n "${DB_PASSWORD:-}" ]; then
    ssh "${SSH_OPTS[@]}" "$REMOTE" bash -s <<REMOTE_ENV
set -euo pipefail
cd "$REMOTE_PATH"
sed -i 's|^APP_URL=.*|APP_URL=https://facturation.easygest.org|' .env
sed -i 's|^APP_ENV=.*|APP_ENV=production|' .env
sed -i 's|^APP_DEBUG=.*|APP_DEBUG=false|' .env
sed -i 's|^APP_DEMO_MODE=.*|APP_DEMO_MODE=false|' .env
sed -i 's|^DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE:-u648457710_firstus}|' .env
sed -i 's|^DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME:-u648457710_firstus}|' .env
sed -i 's|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|' .env
REMOTE_ENV
fi

echo "==> Installation Laravel sur le serveur"
ssh "${SSH_OPTS[@]}" "$REMOTE" "cd '$REMOTE_PATH' && chmod +x scripts/deploy-easygest.sh && ./scripts/deploy-easygest.sh"

if [ -f "$ROOT/deploy/firstus_pos.sql.gz" ] && [ "${IMPORT_DB:-0}" = "1" ]; then
    echo "==> Import base de données"
    scp "${SSH_OPTS[@]}" "$ROOT/deploy/firstus_pos.sql.gz" "$REMOTE:/tmp/firstus_pos.sql.gz"
    ssh "${SSH_OPTS[@]}" "$REMOTE" bash -s <<'IMPORT'
set -euo pipefail
cd "$REMOTE_PATH"
source .env 2>/dev/null || true
gunzip -c /tmp/firstus_pos.sql.gz | mysql -h "${DB_HOST:-localhost}" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"
rm -f /tmp/firstus_pos.sql.gz
IMPORT
fi

echo ""
echo "✅ Déploiement terminé : https://facturation.easygest.org/login"
