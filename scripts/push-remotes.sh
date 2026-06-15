#!/usr/bin/env bash
# Pousse la branche main vers GitLab (origin) et GitHub (github).
set -euo pipefail

cd "$(dirname "$0")/.."
BRANCH="${1:-main}"

echo "==> Push GitLab (origin)..."
git push origin "${BRANCH}"

echo "==> Push GitHub (github)..."
git push github "${BRANCH}"

echo "==> Terminé."
