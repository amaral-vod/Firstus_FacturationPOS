# Hébergement — facturation.easygest.org (Hostinger / easygest.org)

**Sous-domaine :** https://facturation.easygest.org  
**Répertoire serveur :** `/home/u648457710/domains/easygest.org/public_html/facturation`

---

## Architecture

```
public_html/facturation/          ← racine Laravel (code complet)
├── app/
├── bootstrap/
├── config/
├── database/
├── public/                       ← DOCUMENT ROOT du sous-domaine (obligatoire)
│   ├── index.php
│   └── .htaccess
├── storage/
├── vendor/
├── .env
└── ...
```

> **Important :** dans hPanel, le sous-domaine `facturation` doit pointer vers  
> `public_html/facturation/public`  
> **et non** vers `public_html/facturation` seul.

---

## Étape 1 — Créer le sous-domaine (hPanel)

1. **Domaines** → **Sous-domaines**
2. Sous-domaine : `facturation`
3. Dossier : `public_html/facturation/public` *(si l’interface le permet)*  
   Sinon dossier `public_html/facturation` puis voir **Étape 6 (secours)**.
4. Activer **SSL** (Let’s Encrypt) pour `facturation.easygest.org`

---

## Étape 2 — Base MySQL (hPanel)

1. **Bases de données MySQL** → **Créer**
2. Exemple :
   - Base : `u648457710_firstus`
   - Utilisateur : `u648457710_firstus`
   - Mot de passe : *(notez-le)*
3. Associer l’utilisateur à la base (tous privilèges)

---

## Étape 3 — Envoyer le code

### Option A — Git (recommandé, SSH)

```bash
cd /home/u648457710/domains/easygest.org/public_html
git clone git@github.com:amaral-vod/Firstus_FacturationPOS.git facturation
cd facturation
```

### Option B — GitLab

```bash
git clone git@gitlab.com:frioldfr/Firstus-FacturationPOS.git facturation
```

### Option C — Upload ZIP

1. En local :
   ```bash
   cd /var/www/html/Firstus_FacturationPOS
   composer install --no-dev --optimize-autoloader
   zip -r firstus.zip . -x "*.git*" "node_modules/*" ".env"
   ```
2. Uploadez et décompressez dans `public_html/facturation/`

---

## Étape 4 — Fichier `.env` sur le serveur

```bash
cd /home/u648457710/domains/easygest.org/public_html/facturation
cp .env.production.easygest.example .env
nano .env
```

Adaptez :

```env
APP_URL=https://facturation.easygest.org
APP_ENV=production
APP_DEBUG=false
APP_DEMO_MODE=false

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=u648457710_firstus
DB_USERNAME=u648457710_firstus
DB_PASSWORD=votre_mot_de_passe_hostinger
```

Générez la clé :

```bash
php artisan key:generate --force
```

---

## Étape 5 — Installation (SSH)

```bash
cd /home/u648457710/domains/easygest.org/public_html/facturation
chmod +x scripts/deploy-easygest.sh
./scripts/deploy-easygest.sh
```

Import produits (si CSV sur le serveur) :

```bash
php artisan import:products --replace \
  --categories=/chemin/product_categories.csv \
  --products=/chemin/products.csv
```

---

## Étape 6 — Si la racine web ne peut pas être `public/`

Créez `/home/u648457710/domains/easygest.org/public_html/facturation/.htaccess` *(à la racine Laravel, un niveau au-dessus de public)* :

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

Et dans hPanel, pointez le sous-domaine vers `public_html/facturation` (sans `/public`).

---

## Étape 7 — Vérifications

| Test | URL attendue |
|------|----------------|
| Santé Laravel | https://facturation.easygest.org/up |
| Connexion | https://facturation.easygest.org/login |

```bash
php artisan db:show
php artisan tinker --execute="echo App\Models\Product::count().' produits';"
```

---

## Sécurité production

- [ ] `APP_DEBUG=false`
- [ ] `APP_DEMO_MODE=false` (masque les comptes test sur la page login)
- [ ] Changer tous les mots de passe utilisateurs
- [ ] SSL activé (HTTPS)
- [ ] `.env` jamais accessible (vérifier https://facturation.easygest.org/.env → doit être **403/404**)

---

## Mise à jour après modification locale

```bash
# En local
git push

# Sur le serveur
cd /home/u648457710/domains/easygest.org/public_html/facturation
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Dépannage

| Problème | Solution |
|----------|----------|
| 403 Forbidden | Racine web → `.../facturation/public` ; permissions `storage` 775 |
| 500 erreur | Vérifier `.env`, logs `storage/logs/laravel.log` |
| CSS/liens cassés | `APP_URL=https://facturation.easygest.org` |
| Page blanche | `APP_DEBUG=true` temporairement, lire le log |
| Composer absent | Uploader le dossier `vendor/` depuis votre PC |

---

## Contact technique

- Dépôt GitHub : https://github.com/amaral-vod/Firstus_FacturationPOS
- Import CSV : `docs/MIGRATION-MYSQL.md`
