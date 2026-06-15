#!/usr/bin/env bash
# Utilise la clé SSH de frioldfr (fonctionne aussi si vous lancez en root).
export GIT_SSH_COMMAND='ssh -i /home/frioldfr/.ssh/id_ed25519_gitlab -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new'
