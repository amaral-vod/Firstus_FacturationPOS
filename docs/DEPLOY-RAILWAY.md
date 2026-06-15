# Déploiement Railway — Firstus Facturation POS

Guide pour héberger l'application en **tests / démonstrations** via [Railway](https://railway.app), avec le code sur **GitLab**.

---

## Architecture

```
GitLab (frioldfr/Firstus-FacturationPOS)
        │
        │ push sur main
        ▼
   Railway — Service Web (Dockerfile)
        │
        ├── PostgreSQL (service Railway)
        └── URL HTTPS *.up.railway.app
```

---

## Étape 1 — Compte Railway

1. Créez un compte sur https://railway.app
2. **New Project** → **Deploy from Git repo**
3. Choisissez **GitLab** et autorisez l'accès
4. Sélectionnez le dépôt : `frioldfr/Firstus-FacturationPOS`
5. Branche : `main`

Railway détecte le `Dockerfile` et `railway.toml` à la racine.

---

## Étape 2 — Base PostgreSQL

1. Dans le projet Railway : **+ New** → **Database** → **PostgreSQL**
2. Notez le nom du service (ex. `Postgres`)
3. Railway crée automatiquement `DATABASE_URL`

---

## Étape 3 — Variables d'environnement (service Web)

Dans le service **Web** → **Variables**, ajoutez :

| Variable | Valeur |
|----------|--------|
| `APP_NAME` | `Firstus FacturationPOS` |
| `APP_ENV` | `staging` |
| `APP_DEBUG` | `false` |
| `APP_DEMO_MODE` | `true` |
| `APP_KEY` | Voir ci-dessous |
| `APP_URL` | URL générée par Railway (ex. `https://xxx.up.railway.app`) |
| `DB_CONNECTION` | `pgsql` |
| `DB_URL` | `${{Postgres.DATABASE_URL}}` *(adapter le nom du service)* |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `RUN_SEEDERS` | `true` *(premier déploiement uniquement, puis `false`)* |

### Générer APP_KEY

Sur votre machine :

```bash
cd /var/www/html/Firstus_FacturationPOS
php artisan key:generate --show
```

Copiez la valeur `base64:...` dans Railway.

### APP_URL

Après le premier déploiement, Railway affiche une URL publique.  
Copiez-la dans `APP_URL`, puis **redéployez** une fois.

---

## Étape 4 — Domaine public

1. Service Web → **Settings** → **Networking**
2. **Generate Domain** → vous obtenez `https://xxxx.up.railway.app`
3. Mettez à jour `APP_URL` avec cette URL

---

## Étape 5 — Vérification

| Test | URL |
|------|-----|
| Santé Laravel | `https://VOTRE-URL/up` |
| Connexion | `https://VOTRE-URL/login` |

Comptes de démo (si `APP_DEMO_MODE=true`) : mot de passe `password`  
Ex. `comptable@firstus.com`, `caissier@firstus.com`

---

## Déploiements automatiques

Chaque `git push` sur `main` relance un déploiement Railway (si activé dans Settings → Deploy).

```bash
git add .
git commit -m "Ma modification"
git push origin main
```

---

## Importer vos données locales (optionnel)

Exporter depuis votre serveur :

```bash
pg_dump -U firstus -h 127.0.0.1 firstus_pos > backup.sql
```

Sur Railway, utilisez **Connect** sur le service Postgres pour récupérer les identifiants, puis :

```bash
psql "postgresql://user:pass@host:port/railway" < backup.sql
```

---

## Coûts indicatifs

Railway propose un crédit mensuel gratuit (vérifiez sur railway.app/pricing).  
Pour des démos légères, cela suffit souvent. Arrêtez le projet quand vous n'en avez plus besoin.

---

## Dépannage

| Problème | Solution |
|----------|----------|
| `APP_KEY` manquant | Ajouter la variable et redéployer |
| Erreur 500 DB | Vérifier `DB_URL=${{Postgres.DATABASE_URL}}` |
| CSS/liens cassés | Vérifier que `APP_URL` correspond à l'URL Railway |
| Seeders à chaque deploy | Passer `RUN_SEEDERS=false` après le 1er déploiement |
| Logs | Railway → service Web → **Deployments** → **View logs** |

---

## GitLab CI (optionnel, plus tard)

Vous pouvez ajouter un pipeline GitLab qui déclenche Railway via webhook ou l'API Railway.  
Pour commencer, la connexion GitLab directe dans Railway est suffisante.
