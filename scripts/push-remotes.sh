#!/usr/bin/env bash
# Pousse la branche main vers GitLab (origin) et GitHub (github).
set -euo pipefail

cd "$(dirname "$0")/.."
# shellcheck source=git-ssh-env.sh
source "$(dirname "$0")/git-ssh-env.sh"

BRANCH="${1:-main}"

echo "==> Test GitLab..."
ssh -i /home/frioldfr/.ssh/id_ed25519_gitlab -o IdentitiesOnly=yes -T git@gitlab.com 2>&1 | head -1 || true

echo "==> Test GitHub..."
if ! ssh -i /home/frioldfr/.ssh/id_ed25519_gitlab -o IdentitiesOnly=yes -T git@github.com 2>&1 | grep -q "successfully authenticated\|Hi "; then
    echo ""
    echo "ERREUR GitHub : clé SSH non enregistrée sur https://github.com/settings/keys"
    echo "Collez : $(cat /home/frioldfr/.ssh/id_ed25519_gitlab.pub)"
    echo ""
    GITHUB_OK=false
else
    GITHUB_OK=true
fi

echo "==> Push GitLab (origin)..."
git push origin "${BRANCH}"

if [ "${GITHUB_OK}" = "true" ]; then
    echo "==> Push GitHub (github)..."
    git push github "${BRANCH}"
    echo "==> Terminé (GitLab + GitHub)."
else
    echo "==> GitLab OK. GitHub ignoré — ajoutez la clé SSH puis relancez : git push github ${BRANCH}"
fi
