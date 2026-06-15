# Migration PostgreSQL → MySQL

## 1. Prérequis

- MySQL ou MariaDB actif (`systemctl status mysql`)
- Extension PHP `pdo_mysql` (`php -m | grep pdo_mysql`)

## 2. Créer la base MySQL

```bash
cd /var/www/html/Firstus_FacturationPOS
sudo mysql < database/setup_mysql.sql
```

Ou tout-en-un :

```bash
chmod +x scripts/setup-mysql.sh
./scripts/setup-mysql.sh
```

## 3. Configurer `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=firstus_pos
DB_USERNAME=firstus
DB_PASSWORD=firstus123
```

## 4. Migrer le schéma

Nouvelle installation (données de démo) :

```bash
php artisan migrate:fresh --force --seed
```

Installation existante (conserver les migrations déjà jouées) :

```bash
php artisan migrate --force
```

## 5. Importer les données depuis PostgreSQL (optionnel)

Exporter depuis PostgreSQL :

```bash
pg_dump -U firstus -h 127.0.0.1 firstus_pos --data-only --inserts > pg_data.sql
```

Les types et syntaxes diffèrent entre PostgreSQL et MySQL : une conversion manuelle ou un outil ETL peut être nécessaire pour les grosses bases.

Pour repartir proprement avec les seeders + import CSV produits :

```bash
php artisan migrate:fresh --force --seed
php artisan import:products --replace
```

## 6. Vérification

```bash
php artisan db:show
php artisan tinker --execute="echo App\Models\User::count().' utilisateurs';"
```

## 7. Railway / hébergement

Sur Railway, ajoutez un service **MySQL** au lieu de PostgreSQL et définissez :

```env
DB_CONNECTION=mysql
DB_URL=${{MySQL.DATABASE_URL}}
```
