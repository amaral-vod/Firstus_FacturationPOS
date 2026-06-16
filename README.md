# Firstus Facturation POS

Application de gestion commerciale : caisse (POS), stock, facturation proforma, clients, fournisseurs.

**Dépôts :**
- GitLab : https://gitlab.com/frioldfr/Firstus-FacturationPOS
- GitHub : https://github.com/amaral-vod/Firstus_FacturationPOS

**Développement local :** [docs/TRAVAIL-LOCAL.md](docs/TRAVAIL-LOCAL.md) — `./scripts/dev-local.sh`  
**Tous les liens :** [docs/LIENS.md](docs/LIENS.md)  
**Hébergement production :** [docs/DEPLOY-EASYGEST.md](docs/DEPLOY-EASYGEST.md) — `https://facturation.easygest.org`

**Manuel utilisateur :**
- [docs/MANUEL-UTILISATION.md](docs/MANUEL-UTILISATION.md)
- [docs/MANUEL-UTILISATION.docx](docs/MANUEL-UTILISATION.docx) (Word)

---

## Accès à l'application

| Accès | URL | Commande |
|-------|-----|----------|
| **Local (prioritaire)** | http://127.0.0.1:8000/login | `./scripts/dev-local.sh` |
| Réseau LAN | http://192.168.1.79:8000/login | `./start-reseau.sh` |

**Tous les liens :** [docs/LIENS.md](docs/LIENS.md)

Démarrer le serveur local :

```bash
cd /var/www/html/Firstus_FacturationPOS
./scripts/dev-local.sh
```

---

## Comptes de test

Mot de passe pour tous : `password`

| Rôle | Email |
|------|-------|
| Super Admin | admin@firstus.com |
| Admin | admin2@firstus.com |
| Caissier | caissier@firstus.com |
| Magasinier | magasinier@firstus.com |
| Comptable | comptable@firstus.com |
| Logisticien | logisticien@firstus.com |

---

## Modules & liens

| Module | Route |
|--------|-------|
| Tableau de bord | `/dashboard` |
| POS / Caisse | `/caisse` |
| Sessions caisse | `/caisse/sessions` |
| Historique ventes | `/caisse/historique` |
| Facturation | `/facturation` |
| Nouvelle proforma | `/facturation/proforma/nouvelle` |
| Proformas | `/facturation?type=proforma` |
| Clients | `/clients` |
| Crédits clients | `/clients/credits` |
| Stock | `/stock` |
| Mouvements stock | `/stock/mouvements` |
| Produits | `/admin/products` |
| Catégories | `/admin/categories` |
| Fournisseurs | `/fournisseurs` |
| Règlements fournisseurs | `/fournisseurs/reglements` |
| Retours | `/retours` |
| Annulations | `/annulations` |
| Rapports | `/rapports` |
| Journal / Audit | `/journal` |
| Notifications | `/notifications` |
| Utilisateurs | `/admin/users` |
| Rôles | `/admin/roles` |
| Permissions | `/admin/permissions` |
| Groupes | `/admin/groups` |
| Historique connexions | `/admin/connexions` |
| Sécurité | `/securite` |
| Paramètres | `/parametres` |

---

## Installation

```bash
git clone git@gitlab.com:frioldfr/Firstus-FacturationPOS.git
cd Firstus-FacturationPOS
composer install
cp .env.example .env
php artisan key:generate
# Configurer .env (DB PostgreSQL)
php artisan migrate --force
php artisan db:seed --force
php artisan serve --host=0.0.0.0 --port=8000
```

### Base MySQL / MariaDB

```bash
sudo mysql < database/setup_mysql.sql
```

Variables `.env` :

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=firstus_pos
DB_USERNAME=firstus
DB_PASSWORD=firstus123
```

### Base PostgreSQL (legacy)

Voir `database/setup_postgres.sql` si vous conservez PostgreSQL.

### Import produits CSV

```bash
php artisan import:products --replace
```

---

## Facture proforma

- Création : **Facturation → Nouvelle proforma**
- Formats : **A4**, **A5**, **Ticket 80 mm** ou **automatique** selon le nombre de lignes
- Impression : générée à la création ou via le bouton 🖨️

---

## GitLab & GitHub

| Plateforme | URL |
|------------|-----|
| GitLab | https://gitlab.com/frioldfr/Firstus-FacturationPOS |
| GitHub | https://github.com/amaral-vod/Firstus_FacturationPOS |

Pousser sur **les deux** dépôts :

```bash
git add .
git commit -m "Description des changements"
git push origin main    # GitLab
git push github main    # GitHub
```

Ou en une commande : `./scripts/push-remotes.sh`

---

## Déploiement Railway (tests & démos)

Guide détaillé : [docs/DEPLOY-RAILWAY.md](docs/DEPLOY-RAILWAY.md)

### Résumé rapide

1. **railway.app** → New Project → Deploy from **GitLab** → `frioldfr/Firstus-FacturationPOS`
2. Ajouter un service **PostgreSQL**
3. Variables du service Web (voir `.env.railway.example`) :

| Variable | Valeur |
|----------|--------|
| `APP_ENV` | `staging` |
| `APP_DEMO_MODE` | `true` |
| `APP_KEY` | `php artisan key:generate --show` |
| `APP_URL` | URL Railway (`https://xxx.up.railway.app`) |
| `DB_CONNECTION` | `pgsql` |
| `DB_URL` | `${{Postgres.DATABASE_URL}}` |
| `RUN_SEEDERS` | `true` puis `false` |

4. **Networking** → Generate Domain
5. Tester : `https://VOTRE-URL/up` et `/login`

Chaque `git push` sur `main` redéploie automatiquement.

---

## Stack technique

- Laravel 13
- PostgreSQL
- Bootstrap 5
- PHP 8.x

---

## Contact

**frioldfr@gmail.com**
