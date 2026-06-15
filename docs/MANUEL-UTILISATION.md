# Manuel d'utilisation — Firstus Facturation POS

**Version :** 1.0  
**Application :** Firstus Facturation POS  
**Technologie :** Laravel 13 — Gestion commerciale & point de vente  
**Contact :** frioldfr@gmail.com

---

## Table des matières

1. [Présentation](#1-présentation)
2. [Accès à l'application](#2-accès-à-lapplication)
3. [Interface générale](#3-interface-générale)
4. [Rôles et permissions](#4-rôles-et-permissions)
5. [Démarrage de la journée (caissier)](#5-démarrage-de-la-journée-caissier)
6. [Module POS / Caisse](#6-module-pos--caisse)
7. [Gestion de caisse (ouverture / fermeture)](#7-gestion-de-caisse-ouverture--fermeture)
8. [Historique des ventes et tickets](#8-historique-des-ventes-et-tickets)
9. [Facturation et proformas](#9-facturation-et-proformas)
10. [Clients et crédits](#10-clients-et-crédits)
11. [Stock et inventaire](#11-stock-et-inventaire)
12. [Produits et catégories](#12-produits-et-catégories)
13. [Fournisseurs et règlements](#13-fournisseurs-et-règlements)
14. [Retours clients](#14-retours-clients)
15. [Annulations de factures](#15-annulations-de-factures)
16. [Tableau de bord](#16-tableau-de-bord)
17. [Rapports et statistiques](#17-rapports-et-statistiques)
18. [Notifications](#18-notifications)
19. [Administration](#19-administration)
20. [Paramètres entreprise](#20-paramètres-entreprise)
21. [Sécurité et audit](#21-sécurité-et-audit)
22. [Import de produits (CSV)](#22-import-de-produits-csv)
23. [Dépannage (FAQ)](#23-dépannage-faq)
24. [Annexes](#24-annexes)

---

## 1. Présentation

**Firstus Facturation POS** est une application web de gestion commerciale destinée aux boutiques, superettes et PME. Elle couvre :

- **Point de vente (POS)** : ventes rapides, panier, remises, paiement
- **Caisse** : ouverture / fermeture, fonds, écarts
- **Stock** : entrées, sorties, inventaire, alertes stock faible
- **Facturation** : factures proforma (A4, A5, ticket), devis, bons
- **Clients** : fiches, crédits, dettes
- **Fournisseurs** : suivi des dettes et règlements
- **Retours & annulations** : avec remise en stock automatique
- **Rapports** : CA, produits vendus, marges
- **Administration** : utilisateurs, rôles, permissions, groupes

---

## 2. Accès à l'application

### 2.1 URLs d'accès

| Contexte | URL |
|----------|-----|
| Réseau local | `http://192.168.1.79:8000/login` |
| Machine locale | `http://127.0.0.1:8000/login` |
| Démo en ligne (Railway) | URL fournie après déploiement |

### 2.2 Démarrer le serveur (administrateur)

```bash
cd /var/www/html/Firstus_FacturationPOS
./start-reseau.sh
```

### 2.3 Connexion

1. Ouvrez la page **Connexion**
2. Saisissez votre **email** et **mot de passe**
3. Cliquez sur **Se connecter**

> En **mode démonstration** (`APP_DEMO_MODE=true`), la page affiche la liste des comptes test et les liens des modules. Cliquez sur un compte pour remplir automatiquement le formulaire.

### 2.4 Sécurité de connexion

- Après **5 tentatives échouées**, le compte est **bloqué 15 minutes**
- Toutes les connexions sont enregistrées (succès et échecs)
- Utilisez **Déconnexion** en haut à droite en fin de session

---

## 3. Interface générale

Une fois connecté, l'écran se compose de :

| Zone | Description |
|------|-------------|
| **Menu latéral (gauche)** | Navigation par module ; les entrées visibles dépendent de votre rôle |
| **Barre supérieure** | Titre de la page, votre nom, badge de rôle, bouton déconnexion |
| **Zone centrale** | Contenu du module actif |
| **Messages** | Bandeaux verts (succès) ou rouges (erreur) en haut du contenu |

### Sections du menu

| Section | Modules |
|---------|---------|
| **Commercial** | POS, Caisse, Ventes, Facturation, Proforma, Clients, Crédits |
| **Stocks** | Stock, Produits, Catégories, Fournisseurs |
| **Retours** | Retours, Annulations |
| **Analyse** | Rapports, Audit, Notifications |
| **Administration** | Utilisateurs, Rôles, Permissions, Groupes, Connexions, Sécurité, Paramètres |

---

## 4. Rôles et permissions

Chaque utilisateur a **un rôle** qui détermine les modules accessibles.

| Rôle | Missions principales |
|------|----------------------|
| **Super Administrateur** | Accès total à tous les modules |
| **Administrateur** | Accès total (gestion complète) |
| **Caissier** | POS, caisse, ventes, facturation de base, clients |
| **Magasinier** | Stock, produits, catégories, fournisseurs, retours |
| **Comptable** | Facturation, clients, crédits, fournisseurs, rapports, audit |
| **Logisticien** | Stock (consultation), bons commande/livraison, fournisseurs, retours |

### Comptes de démonstration

Mot de passe pour tous : **`password`**

| Rôle | Email |
|------|-------|
| Super Admin | `admin@firstus.com` |
| Admin | `admin2@firstus.com` |
| Caissier | `caissier@firstus.com` |
| Magasinier | `magasinier@firstus.com` |
| Comptable | `comptable@firstus.com` |
| Logisticien | `logisticien@firstus.com` |

> **Production :** changez impérativement tous les mots de passe et désactivez `APP_DEMO_MODE`.

---

## 5. Démarrage de la journée (caissier)

Workflow recommandé pour un caissier :

```
1. Connexion
      ↓
2. Ouverture de caisse (fonds initial)
      ↓
3. Ventes au POS
      ↓
4. Impression des tickets
      ↓
5. Fermeture de caisse (fond réel + écart)
      ↓
6. Déconnexion
```

---

## 6. Module POS / Caisse

**Menu :** Commercial → **POS**  
**URL :** `/caisse`

### 6.1 Écran de vente

L'écran est divisé en deux :

**Partie gauche — Catalogue produits**
- Grille de produits avec nom, prix, stock
- Barre de **recherche** par nom
- Filtre par **catégorie**
- Les produits **sans stock** apparaissent grisés (non vendables)
- Les produits en **promotion** affichent un badge 🔥

**Partie droite — Panier**
- Liste des articles ajoutés
- Modification des quantités (+ / −)
- Suppression d'une ligne
- **Remise** en FCFA
- **Total** calculé automatiquement

### 6.2 Enregistrer une vente

1. Cliquez sur un **produit** pour l'ajouter au panier
2. Ajustez les **quantités** si nécessaire
3. Saisissez une **remise** (optionnel)
4. Choisissez le **mode de paiement** :
   - 💵 Espèces
   - 📱 Mobile Money
   - 💳 Carte
5. Saisissez le **montant payé** par le client
6. Vérifiez la **monnaie** à rendre
7. Cliquez **Valider la vente**

### 6.3 Comportements automatiques

- Le **stock** est diminué à chaque vente validée
- Une **facture / ticket** est généré avec un numéro unique
- Impossible de vendre plus que le stock disponible
- Le prix appliqué est le **prix promo** s'il est actif, sinon le prix normal

### 6.4 Erreurs fréquentes

| Message | Cause | Action |
|---------|-------|--------|
| Stock insuffisant | Quantité demandée > stock | Réduire la quantité ou réapprovisionner |
| Panier vide | Aucun article | Ajouter au moins un produit |

---

## 7. Gestion de caisse (ouverture / fermeture)

**Menu :** Commercial → **Ouverture/Fermeture**  
**URL :** `/caisse/sessions`

### 7.1 Ouvrir la caisse

1. Accédez au module **Gestion de caisse**
2. Saisissez le **fonds initial** (espèces en caisse au début)
3. Cliquez **Ouvrir la caisse**

> Une seule session ouverte par caissier à la fois.

### 7.2 Fermer la caisse

1. Comptez physiquement l'argent en caisse
2. Saisissez le **fond réel**
3. Ajoutez des **notes** (optionnel)
4. Cliquez **Fermer la caisse**

Le système calcule l'**écart** :
- **Surplus** (vert) : plus d'argent que prévu
- **Manquant** (rouge) : moins d'argent que prévu

### 7.3 Historique

Le tableau **Historique sessions** liste toutes les sessions passées : caissier, heures, écart, statut.

---

## 8. Historique des ventes et tickets

**Menu :** Commercial → **Ventes**  
**URL :** `/caisse/historique`

### 8.1 Consulter une vente

- Liste paginée des ventes : numéro, total, date, caissier
- Cliquez **Voir** pour le détail des lignes

### 8.2 Réimprimer un ticket

1. Depuis le détail d'une vente ou la facturation
2. Cliquez **Imprimer** / 🖨️
3. Le ticket s'ouvre dans une nouvelle fenêtre
4. Utilisez **Imprimer** du navigateur ou `Ctrl+P`

### 8.3 Contenu du ticket (58 mm ou 80 mm)

- En-tête magasin (nom, adresse, téléphone)
- Numéro de facture et date/heure
- Détail des lignes (produit, qté × prix, total ligne)
- **Nb total d'articles**
- Sous-total, remise, **TOTAL**
- Mode de paiement, montant payé, monnaie
- Nom du caissier

> Le format ticket (58 ou 80 mm) se configure dans **Paramètres → Format ticket**.

---

## 9. Facturation et proformas

**Menu :** Commercial → **Facturation** / **Proforma**  
**URL :** `/facturation`

### 9.1 Types de documents

| Type | Description |
|------|-------------|
| **Proforma** | Devis officiel avant vente ; sans valeur comptable |
| **Devis** | Proposition commerciale |
| **Bon de commande** | Commande fournisseur / client |
| **Bon de livraison** | Preuve de livraison |
| **Facture A4** | Facture définitive format page |
| **Ticket** | Lié à une vente caisse |

### 9.2 Créer une facture proforma

**Menu :** **Proforma** ou `/facturation/proforma/nouvelle`

1. **Client** : sélectionnez un client enregistré ou saisissez nom / adresse / téléphone
2. **Lignes** :
   - Choisissez un produit catalogue **ou** saisissez une désignation libre
   - Indiquez quantité et prix unitaire
   - Cliquez **+ Ajouter une ligne** pour d'autres articles
3. **Format papier** :
   - **Automatique** (recommandé) : choix selon le volume
   - **Ticket 80 mm** : peu de lignes
   - **A5** : format intermédiaire
   - **A4** : beaucoup de lignes ou notes longues
4. **Date validité**, **remise**, **notes** (optionnels)
5. Cliquez **Créer et imprimer**

### 9.3 Règles de format automatique

| Situation | Format choisi |
|-----------|---------------|
| ≤ 2 lignes, peu d'articles, pas de notes | Ticket 80 mm |
| ≤ 5 lignes, ≤ 10 articles | A5 |
| Sinon | A4 |

### 9.4 Liste et réimpression

- **Facturation** → filtre **Proformas**
- Boutons **Voir** 👁️ et **Imprimer** 🖨️
- En mode auto, liens pour réimprimer en A4, A5 ou Ticket

### 9.5 Calcul des totaux proforma

```
Sous-total = Σ (quantité × prix unitaire)
Base       = Sous-total − Remise
TVA        = Base × taux TVA (si configuré)
Total      = Base + TVA
```

Le taux TVA se règle dans **Paramètres → TVA (%)**.

---

## 10. Clients et crédits

**Menu :** Commercial → **Clients**  
**URL :** `/clients`

### 10.1 Liste des clients

Affiche : code, nom, téléphone, **dette actuelle**, **plafond crédit**.

### 10.2 Ajouter un client

1. Panneau **Nouveau client** (à droite)
2. Renseignez : nom (obligatoire), téléphone, email, plafond crédit
3. Cliquez **Ajouter**

### 10.3 Module crédits

**Menu :** **Crédits** — `/clients/credits`

- Suivi des crédits clients accordés
- Dettes en retard
- Total des dettes (également visible sur la page Clients)

---

## 11. Stock et inventaire

**Menu :** Stocks → **Stock**  
**URL :** `/stock`

### 11.1 État du stock

Tableau : produit, catégorie, quantité, seuil minimum, statut (OK / Faible).

Une **alerte orange** s'affiche en haut si des produits sont sous le seuil.

### 11.2 Entrée de stock

Utilisez après réception marchandise :

1. Sélectionnez le **produit**
2. Saisissez la **quantité** reçue
3. Notes (ex. « Livraison fournisseur X »)
4. **Enregistrer**

### 11.3 Sortie de stock

Utilisez pour casse, perte, échantillon :

1. Produit + quantité + motif
2. **Enregistrer**

### 11.4 Inventaire

Pour corriger le stock après comptage physique :

1. Sélectionnez le produit
2. Saisissez la **quantité réelle** comptée
3. Le système ajuste l'écart automatiquement

### 11.5 Mouvements de stock

**Menu :** bouton **Mouvements** — `/stock/mouvements`

Historique complet : entrées, sorties, ventes, retours, inventaires, avec date et utilisateur.

---

## 12. Produits et catégories

### 12.1 Produits

**Menu :** Stocks → **Produits** — `/admin/products`

| Action | Procédure |
|--------|-----------|
| **Rechercher** | Barre de recherche + bouton 🔍 |
| **Créer** | Formulaire à droite : nom, SKU, catégorie, prix, coût, promo |
| **Modifier** | Bouton ✏️ sur la ligne |
| **Supprimer** | Bouton 🗑️ (confirmation demandée) |

Champs importants :
- **Prix** : prix de vente normal
- **Coût** : prix d'achat (pour la marge)
- **Prix promo** : prix promotionnel (avec dates début/fin si configurées)
- **SKU** : référence unique produit

### 12.2 Catégories

**Menu :** Stocks → **Catégories** — `/admin/categories`

Organisez le catalogue (Alimentation, Boissons, etc.) pour filtrer le POS et les rapports.

---

## 13. Fournisseurs et règlements

**Menu :** Stocks → **Fournisseurs** — `/fournisseurs`

### 13.1 Gestion fournisseurs

- Liste : code, nom, téléphone, **solde dû**
- Ajout via formulaire **Nouveau fournisseur**

### 13.2 Règlements

**Menu :** lien **Règlements** — `/fournisseurs/reglements`

Enregistrez les paiements effectués aux fournisseurs pour mettre à jour le solde.

---

## 14. Retours clients

**Menu :** Retours → **Retours** — `/retours`

### 14.1 Créer un retour

1. **Nouveau retour** ou `/retours/create`
2. Sélectionnez la **facture / vente** concernée
3. Choisissez le type :
   - **Retour total** : tous les articles
   - **Retour partiel** : sélection des articles et quantités
4. Saisissez un **motif** (minimum 10 caractères, obligatoire)
5. **Enregistrer le retour**

### 14.2 Effets automatiques

- Les articles retournés sont **remis en stock**
- Le retour est tracé dans l'historique et le journal d'audit

---

## 15. Annulations de factures

**Menu :** Retours → **Annulations** — `/annulations`

> **Attention :** l'annulation est une opération sensible. Elle annule définitivement la vente.

### Procédure

1. Sélectionnez la **facture** à annuler
2. Saisissez un **motif** (min. 10 caractères)
3. Confirmez l'annulation

### Effets automatiques

- **Remise en stock** de tous les articles de la facture
- Enregistrement dans l'**historique des annulations**
- Trace dans le **journal de sécurité**

---

## 16. Tableau de bord

**Menu :** **Tableau de bord** — `/dashboard`

### Indicateurs affichés

| Indicateur | Description |
|------------|-------------|
| CA Jour | Chiffre d'affaires du jour |
| Bénéfice Jour | Marge estimée (ventes − coût) |
| CA Mois | Cumul mensuel |
| Ventes | Nombre de ventes du jour |
| Dettes clients | Total des créances |
| Dettes fournisseurs | Total à payer |
| Top produits | Articles les plus vendus |
| Stocks critiques | Produits sous le seuil minimum |
| Crédits en retard | Nombre de crédits échus |
| Factures en attente | Documents non soldés |
| Retours / Annulations | Statistiques du mois |

---

## 17. Rapports et statistiques

**Menu :** Analyse → **Rapports & BI** — `/rapports`

### Périodes disponibles

- **Jour** — **Mois** — **Année** (boutons en haut)

### Données

- Chiffre d'affaires et nombre de ventes
- **Produits les plus vendus** (quantité et CA)
- **Marges** par produit
- Répartition par **catégorie**
- **Modes de paiement** (espèces, mobile, carte)

---

## 18. Notifications

**Menu :** Analyse → **Notifications** — `/notifications`

- Alertes **stock faible**
- Autres événements système
- Marquer une notification comme lue
- **Tout marquer comme lu**

---

## 19. Administration

### 19.1 Utilisateurs

**Menu :** Administration → **Utilisateurs** — `/admin/users`

| Action | Description |
|--------|-------------|
| Créer | Nom, email, mot de passe, rôle, site, statut actif |
| Modifier | Changer rôle, réinitialiser mot de passe |
| Désactiver / Supprimer | Bloquer l'accès |

### 19.2 Rôles

**Menu :** **Rôles** — `/admin/roles`

- Liste des rôles prédéfinis
- Modification des **permissions** cochées par rôle

### 19.3 Permissions

**Menu :** **Permissions** — `/admin/permissions`

Vue de toutes les permissions disponibles dans le système.

### 19.4 Groupes utilisateurs

**Menu :** **Groupes** — `/admin/groups`

Regroupement d'utilisateurs pour des permissions supplémentaires.

### 19.5 Historique connexions

**Menu :** **Connexions** — `/admin/connexions`

Journal : email, IP, navigateur, succès/échec, heures connexion/déconnexion.

---

## 20. Paramètres entreprise

**Menu :** Administration → **Paramètres** — `/parametres`

### Paramétrage entreprise

| Champ | Usage |
|-------|-------|
| Nom société | En-tête tickets et factures |
| Téléphone, adresse, email | Coordonnées sur documents |
| IFU, RCCM | Identifiants fiscaux (Bénin) |
| Logo | Image sur documents (upload) |

### Paramétrage système

| Champ | Usage |
|-------|-------|
| Devise | Ex. FCFA |
| Langue | Français / English |
| Fuseau horaire | Ex. Africa/Porto-Novo |
| Format ticket | 58 mm ou 80 mm |
| TVA (%) | Taux appliqué aux proformas |

Cliquez **Enregistrer** après modification.

---

## 21. Sécurité et audit

### 21.1 Journal de sécurité

**Menu :** **Sécurité** — `/securite`

- Connexions réussies et **échecs**
- Adresses IP
- Actions sensibles

### 21.2 Journal d'activité (Audit)

**Menu :** **Audit Trail** — `/journal`

Trace des opérations : ventes, stocks, modifications, connexions, etc.

---

## 22. Import de produits (CSV)

Réservé à l'**administrateur système** (ligne de commande).

```bash
cd /var/www/html/Firstus_FacturationPOS
php artisan import:products --replace
```

Fichiers attendus (exemple) :
- `product_categories.csv`
- `products.csv`

Options :
- `--replace` : remplace les produits existants (par SKU)

Après import, vérifiez les **stocks** (initialisés à 0 si non renseignés) et faites les **entrées de stock** nécessaires.

---

## 23. Dépannage (FAQ)

| Problème | Solution |
|----------|----------|
| Page inaccessible | Vérifier que `php artisan serve` ou Apache est démarré |
| Compte bloqué | Attendre 15 min ou demander à un admin de débloquer |
| Module invisible | Votre rôle n'a pas la permission ; contactez l'admin |
| Stock négatif au POS | Produit épuisé ; faire une entrée de stock |
| Ticket mal formaté | Vérifier **Paramètres → Format ticket** (58/80 mm) |
| Proforma TVA incorrecte | Vérifier **Paramètres → TVA (%)** |
| Erreur 419 / session expirée | Reconnectez-vous |
| Liens cassés en ligne | Vérifier `APP_URL` dans `.env` |

---

## 24. Annexes

### 24.1 Raccourcis modules (URLs)

| Module | URL |
|--------|-----|
| Connexion | `/login` |
| Tableau de bord | `/dashboard` |
| POS | `/caisse` |
| Sessions caisse | `/caisse/sessions` |
| Historique ventes | `/caisse/historique` |
| Facturation | `/facturation` |
| Nouvelle proforma | `/facturation/proforma/nouvelle` |
| Clients | `/clients` |
| Crédits | `/clients/credits` |
| Stock | `/stock` |
| Mouvements stock | `/stock/mouvements` |
| Produits | `/admin/products` |
| Catégories | `/admin/categories` |
| Fournisseurs | `/fournisseurs` |
| Règlements | `/fournisseurs/reglements` |
| Retours | `/retours` |
| Annulations | `/annulations` |
| Rapports | `/rapports` |
| Journal | `/journal` |
| Notifications | `/notifications` |
| Utilisateurs | `/admin/users` |
| Rôles | `/admin/roles` |
| Paramètres | `/parametres` |
| Sécurité | `/securite` |

### 24.2 Dépôts code source

| Plateforme | URL |
|------------|-----|
| GitLab | https://gitlab.com/frioldfr/Firstus-FacturationPOS |
| GitHub | https://github.com/amaral-vod/Firstus_FacturationPOS |

### 24.3 Documentation technique

| Document | Fichier |
|----------|---------|
| Déploiement Railway | `docs/DEPLOY-RAILWAY.md` |
| Git / SSH | `docs/GIT-SSH.md` |
| README projet | `README.md` |

---

*Fin du manuel — Firstus Facturation POS*
