# Pousser vers GitLab / GitHub

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

### Solution C — Une commande manuelle (root ou frioldfr)

```bash
export GIT_SSH_COMMAND='ssh -i /home/frioldfr/.ssh/id_ed25519_gitlab -o IdentitiesOnly=yes'
cd /var/www/html/Firstus_FacturationPOS
git push origin main
git push github main
```

---

## GitHub : enregistrer la clé SSH

Tant que `ssh -T git@github.com` renvoie « Permission denied », ajoutez la clé :

1. https://github.com/settings/keys → **New SSH key**
2. Collez le contenu de :

```bash
cat /home/frioldfr/.ssh/id_ed25519_gitlab.pub
```

3. Créez le dépôt vide : https://github.com/new → `Firstus-FacturationPOS`
4. Test :

```bash
ssh -i /home/frioldfr/.ssh/id_ed25519_gitlab -o IdentitiesOnly=yes -T git@github.com
```

5. Push :

```bash
./scripts/push-remotes.sh
```

---

## GitLab

La clé est déjà enregistrée. Test :

```bash
ssh -i /home/frioldfr/.ssh/id_ed25519_gitlab -o IdentitiesOnly=yes -T git@gitlab.com
# → Welcome to GitLab, @frioldfr!
```
