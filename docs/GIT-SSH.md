# Pousser vers GitLab / GitHub

| Dépôt | URL |
|-------|-----|
| GitLab | `git@gitlab.com:frioldfr/Firstus-FacturationPOS.git` |
| GitHub | `git@github.com:amaral-vod/Firstus_FacturationPOS.git` |

---

## Problème « Permission denied (publickey) » en root

Si vous êtes connecté en **root** (`root@frioldfr#`), Git ne trouve pas les clés SSH
(elles sont dans `/home/frioldfr/.ssh/`).

### Solution A — Utiliser le script (recommandé)

```bash
cd /var/www/html/Firstus_FacturationPOS
./scripts/push-remotes.sh
```

### Solution B — Utilisateur frioldfr

```bash
su - frioldfr
cd /var/www/html/Firstus_FacturationPOS
git push origin main
git push github main
```

### Solution C — Commande manuelle (root ou frioldfr)

```bash
export GIT_SSH_COMMAND='ssh -i /home/frioldfr/.ssh/id_ed25519_gitlab -o IdentitiesOnly=yes'
cd /var/www/html/Firstus_FacturationPOS
git push origin main
git push github main
```

---

## GitHub (compte / org **amaral-vod**)

La clé doit être ajoutée sur le **compte GitHub** qui a accès à `amaral-vod`.

1. https://github.com/settings/keys → **New SSH key**
2. Titre : `Firstus-POS-serveur`
3. Collez :

```
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAINgE2Y0PlW9QEiENcqyR+2AwzvL0DU3msBGLt91q0DJ4 frioldfr@gmail.com
```

4. Vérifiez l’empreinte : `SHA256:4xmJjSIG0nh+0LYdW51PoU7xgrkCHAAl8Xt7AgsR1yA`
5. Dépôt : https://github.com/amaral-vod/Firstus_FacturationPOS
6. Test :

```bash
ssh -i /home/frioldfr/.ssh/id_ed25519_gitlab -o IdentitiesOnly=yes -T git@github.com
# → Hi VOTRE_USER! You've successfully authenticated...
```

7. Push :

```bash
./scripts/push-remotes.sh
```

### Alternative HTTPS (token)

Si SSH bloque encore, créez un token : https://github.com/settings/tokens (scope `repo`)

```bash
export GIT_SSH_COMMAND='ssh -i /home/frioldfr/.ssh/id_ed25519_gitlab -o IdentitiesOnly=yes'
git push origin main
git push https://VOTRE_TOKEN@github.com/amaral-vod/Firstus_FacturationPOS.git main
```

---

## GitLab

La clé est déjà enregistrée. Test :

```bash
ssh -i /home/frioldfr/.ssh/id_ed25519_gitlab -o IdentitiesOnly=yes -T git@gitlab.com
# → Welcome to GitLab, @frioldfr!
```
