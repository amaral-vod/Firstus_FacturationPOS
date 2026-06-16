# Liens — Firstus Facturation POS

## Application (local)

| Usage | URL | Commande |
|-------|-----|----------|
| **Local (prioritaire)** | http://127.0.0.1:8000/login | `./scripts/dev-local.sh` |
| Session en cours | http://127.0.0.1:8000/login | — |
| Réseau Wi-Fi / LAN | http://192.168.1.79:8000/login | `./start-reseau.sh` |

> Remplacez `192.168.1.79` par votre IP (`hostname -I`).

---

## Modules (après connexion)

Base : `http://127.0.0.1:8000`

| Module | URL |
|--------|-----|
| Tableau de bord | `/dashboard` |
| POS / Caisse | `/caisse` |
| Ouverture / fermeture caisse | `/caisse/sessions` |
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
| Journal / audit | `/journal` |
| Notifications | `/notifications` |
| Utilisateurs | `/admin/users` |
| Rôles | `/admin/roles` |
| Permissions | `/admin/permissions` |
| Groupes | `/admin/groups` |
| Historique connexions | `/admin/connexions` |
| Sécurité | `/securite` |
| Paramètres | `/parametres` |

---

## Code source

| Plateforme | URL |
|------------|-----|
| GitLab | https://gitlab.com/frioldfr/Firstus-FacturationPOS |
| GitHub | https://github.com/amaral-vod/Firstus_FacturationPOS |

Push les deux dépôts :

```bash
./scripts/push-remotes.sh
```

---

## Documentation

| Document | Fichier local | GitHub |
|----------|---------------|--------|
| Travail local | `docs/TRAVAIL-LOCAL.md` | [lien](https://github.com/amaral-vod/Firstus_FacturationPOS/blob/main/docs/TRAVAIL-LOCAL.md) |
| Manuel utilisateur | `docs/MANUEL-UTILISATION.md` | [lien](https://github.com/amaral-vod/Firstus_FacturationPOS/blob/main/docs/MANUEL-UTILISATION.md) |
| Manuel Word | `docs/MANUEL-UTILISATION.docx` | [lien](https://github.com/amaral-vod/Firstus_FacturationPOS/blob/main/docs/MANUEL-UTILISATION.docx) |
| MySQL | `docs/MIGRATION-MYSQL.md` | [lien](https://github.com/amaral-vod/Firstus_FacturationPOS/blob/main/docs/MIGRATION-MYSQL.md) |
| Git / SSH | `docs/GIT-SSH.md` | [lien](https://github.com/amaral-vod/Firstus_FacturationPOS/blob/main/docs/GIT-SSH.md) |
| Railway (plus tard) | `docs/DEPLOY-RAILWAY.md` | [lien](https://github.com/amaral-vod/Firstus_FacturationPOS/blob/main/docs/DEPLOY-RAILWAY.md) |

---

## Comptes test

Mot de passe : `password`

| Rôle | Email |
|------|-------|
| Super Admin | admin@firstus.com |
| Admin | admin2@firstus.com |
| Caissier | caissier@firstus.com |
| Magasinier | magasinier@firstus.com |
| Comptable | comptable@firstus.com |
| Logisticien | logisticien@firstus.com |

---

## Base de données (local)

| Paramètre | Valeur |
|-----------|--------|
| Moteur | MySQL / MariaDB |
| Hôte | `127.0.0.1:3306` |
| Base | `firstus_pos` |
| Utilisateur | `firstus` |
| Mot de passe | `firstus123` |
