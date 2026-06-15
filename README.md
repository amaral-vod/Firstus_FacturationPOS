# Firstus Facturation POS

Application de gestion commerciale : caisse (POS), stock, facturation proforma, clients, fournisseurs.

**Dépôt GitLab :** https://gitlab.com/frioldfr/Firstus-FacturationPOS

---

## Accès à l'application

| Accès | URL |
|-------|-----|
| Réseau local | http://192.168.1.79:8000/login |
| Localhost | http://127.0.0.1:8000/login |
| Apache (redirect) | http://192.168.1.79/Firstus_FacturationPOS/ |

Démarrer le serveur :

```bash
cd /var/www/html/Firstus_FacturationPOS
./start-reseau.sh
# ou
php artisan serve --host=0.0.0.0 --port=8000
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

### Base PostgreSQL

```sql
CREATE USER firstus WITH PASSWORD 'firstus123';
CREATE DATABASE firstus_pos OWNER firstus;
```

Variables `.env` :

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=firstus_pos
DB_USERNAME=firstus
DB_PASSWORD=firstus123
APP_URL=http://192.168.1.79:8000
```

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

## GitLab

| Lien | URL |
|------|-----|
| Projet | https://gitlab.com/frioldfr/Firstus-FacturationPOS |
| Issues | https://gitlab.com/frioldfr/Firstus-FacturationPOS/-/issues |
| Merge requests | https://gitlab.com/frioldfr/Firstus-FacturationPOS/-/merge_requests |

```bash
git add .
git commit -m "Description des changements"
git push
```

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
