#!/usr/bin/env bash
# Pousse la branche main vers GitLab (origin) et GitHub (github).
set -euo pipefail

cd "$(dirname "$0")/.."
# shellcheck source=git-ssh-env.sh
source "$(dirname "$0")/git-ssh-env.sh"

SSH_KEY="/home/frioldfr/.ssh/id_ed25519_gitlab"
SSH_OPTS=(-i "${SSH_KEY}" -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new)
BRANCH="${1:-main}"

echo "==> Test GitLab..."
ssh "${SSH_OPTS[@]}" -T git@gitlab.com 2>&1 | head -1 || true

echo "==> Test GitHub..."
# GitHub renvoie le code 1 même quand l'auth réussit — ne pas utiliser le code de sortie ssh.
GITHUB_MSG=$(ssh "${SSH_OPTS[@]}" -T git@github.com 2>&1 || true)
if echo "${GITHUB_MSG}" | grep -qE 'successfully authenticated|Hi [^!]+!'; then
    echo "${GITHUB_MSG}" | head -1
    GITHUB_OK=true
else
    echo ""
    echo "ERREUR GitHub : clé SSH non enregistrée sur https://github.com/settings/keys"
    echo "Collez : $(cat "${SSH_KEY}.pub")"
    echo ""
    GITHUB_OK=false
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
