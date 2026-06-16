# Travail en local — Firstus Facturation POS

Priorité : développer et tester **sur votre machine** avant Git push ou déploiement Railway.

---

## Démarrage rapide

```bash
cd /var/www/html/Firstus_FacturationPOS

# 1. Base MySQL (une seule fois)
sudo mysql < database/setup_mysql.sql
php artisan migrate --force --seed

# 2. Lancer l'app en local
chmod +x scripts/dev-local.sh
./scripts/dev-local.sh
```

**URL :** http://127.0.0.1:8000/login

---

## Configuration `.env` (local)

```env
APP_ENV=local
APP_DEBUG=true
APP_DEMO_MODE=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=firstus_pos
DB_USERNAME=firstus
DB_PASSWORD=firstus123
```

---

## Comptes test

Mot de passe : `password`

| Rôle | Email |
|------|-------|
| Admin | admin@firstus.com |
| Caissier | caissier@firstus.com |
| Comptable | comptable@firstus.com |

---

## Commandes utiles au quotidien

```bash
# Serveur local
./scripts/dev-local.sh

# Réseau LAN (autre PC du Wi-Fi)
./start-reseau.sh

# Après modification du code
php artisan config:clear
php artisan view:clear

# Réinitialiser la base de démo
php artisan migrate:fresh --force --seed

# Import produits CSV
php artisan import:products --replace
```

---

## Workflow recommandé

```
1. Coder en local (127.0.0.1)
2. Tester les modules (POS, stock, proforma…)
3. git add / commit
4. ./scripts/push-remotes.sh   ← GitLab + GitHub
5. Railway plus tard (quand prêt)
```

---

## Fichiers de référence

| Document | Contenu |
|----------|---------|
| `docs/MANUEL-UTILISATION.md` | Manuel utilisateur |
| `docs/MIGRATION-MYSQL.md` | Base MySQL |
| `docs/DEPLOY-RAILWAY.md` | En ligne (plus tard) |

---

## Accès depuis un autre appareil (optionnel)

Uniquement si besoin de tester sur téléphone/tablette du même réseau :

```bash
./start-reseau.sh
# → http://VOTRE-IP:8000/login
```

Pour le développement courant, restez sur **`./scripts/dev-local.sh`**.
