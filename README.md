# PNDM — Plateforme Nationale des Données sur la Migration

**Institut National de la Statistique — République du Niger**

> Plateforme web de centralisation, validation et diffusion des données statistiques
> sur la migration au Niger. PHP 8 MVC sans framework, MySQL 8, API REST v1.

---

## Sommaire

1. [Aperçu](#aperçu)
2. [Stack technique](#stack-technique)
3. [Architecture](#architecture)
4. [Installation](#installation)
5. [Configuration](#configuration)
6. [Base de données](#base-de-données)
7. [Comptes par défaut](#comptes-par-défaut)
8. [API REST v1](#api-rest-v1)
9. [Flux éditorial](#flux-éditorial)
10. [Sécurité](#sécurité)
11. [Structure des fichiers](#structure-des-fichiers)

---

## Aperçu

| Métrique | Valeur |
|---|---|
| Indicateurs | 43 |
| Observations migrées | 150 |
| Thématiques | 7 |
| Entités géographiques | 9 (national + 8 régions) |
| Routes publiques | 8 |
| Routes admin | ~30 |
| Endpoints API | 6 |

**Couleurs INS Niger** : Orange `#F4A11D` · Vert `#1DA462` · Bleu `#005B9A`

---

## Stack technique

| Couche | Choix |
|---|---|
| Langage | PHP 8.1+ (`declare(strict_types=1)`) |
| Base de données | MySQL 8 InnoDB `utf8mb4_unicode_ci` |
| Accès données | PDO — 100 % requêtes préparées |
| Dépendances PHP | Composer — PHPMailer 7, endroid/qr-code 5 |
| Graphiques | Chart.js 4 (CDN) |
| Cartographie | Leaflet 1.9 (CDN) |
| CSS | Bootstrap 5.3 (CDN) + styles personnalisés |
| Icônes | Bootstrap Icons 1.11 (CDN) |
| Accessibilité | WCAG 2.1 AA |

---

## Architecture

```
Pattern : Front Controller + MVC sans framework
Autoload : spl_autoload_register PSR-4  (App\ → app/)
Sessions : HttpOnly · Secure · SameSite=Lax · régénération anti-fixation
CSRF     : token session, vérifié sur chaque POST
Audit    : table audit_log — chaque action sensible tracée
Rate limit : MySQL-based (RateLimit.php) — 60 req/min sur l'API
```

### Flux de requête

```
Navigateur → public/index.php → Router::dispatch()
    → [middleware Auth/CSRF] → Controller@method()
        → Model::query() → PDO → MySQL
        → View::renderWithLayout() → layouts/[admin|public].php
```

---

## Installation

### Prérequis

- PHP 8.1+ avec extensions : `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`
- MySQL 8.0+
- Composer 2+

### Étapes

```bash
# 1. Cloner le dépôt
git clone <repo> /home/user/webapp
cd /home/user/webapp

# 2. Installer les dépendances PHP
composer install --no-dev

# 3. Copier et configurer l'environnement
cp .env.example .env
# Éditer .env avec vos valeurs

# 4. Créer la base de données
mysql -u root -p -e "
  CREATE DATABASE pndm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER 'pndm_user'@'localhost' IDENTIFIED BY 'votre_mot_de_passe';
  GRANT ALL PRIVILEGES ON pndm.* TO 'pndm_user'@'localhost';
  FLUSH PRIVILEGES;"

# 5. Importer le schéma et les données de référence
mysql -u pndm_user -p --default-character-set=utf8mb4 pndm < database/schema.sql
mysql -u pndm_user -p --default-character-set=utf8mb4 pndm < database/seed.sql

# 6. (Optionnel) Migrer les données de l'ancienne base
#    Adapter les credentials dans database/migrate_old_data.php
php database/migrate_old_data.php --dry-run   # prévisualisation
php database/migrate_old_data.php             # migration réelle

# 7. Créer les dossiers de stockage
mkdir -p storage/uploads storage/imports storage/exports
chmod 775 storage/uploads storage/imports storage/exports

# 8. Lancer le serveur de développement
php -S 0.0.0.0:8080 -t public/
```

Accès : [http://localhost:8080](http://localhost:8080)
Administration : [http://localhost:8080/admin/login](http://localhost:8080/admin/login)

---

## Configuration

Fichier `.env` à la racine :

```ini
# Application
APP_NAME="PNDM"
APP_ENV=production          # development | production
APP_URL=https://pndm.ins.ne
APP_DEBUG=false

# Base de données
DB_HOST=localhost
DB_PORT=3306
DB_NAME=pndm
DB_USER=pndm_user
DB_PASS=votre_mot_de_passe

# Mail (PHPMailer)
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USER=no-reply@ins.ne
MAIL_PASS=votre_mot_de_passe_smtp
MAIL_FROM=no-reply@ins.ne
MAIL_FROM_NAME="PNDM — INS Niger"

# Sécurité
APP_KEY=                    # 64 caractères hex aléatoires
RATE_LIMIT_WINDOW=60        # secondes
RATE_LIMIT_MAX=60           # requêtes par fenêtre
```

---

## Base de données

### Tables principales (19 au total)

| Table | Rôle |
|---|---|
| `indicateurs` | 43 indicateurs de migration avec métadonnées |
| `observations` | Données saisies (workflow 5 statuts) |
| `thematiques` | 7 thématiques de classification |
| `entites` | Organismes producteurs de données |
| `geo_entites` | Niger national + 8 régions (avec GeoJSON) |
| `frequences` | Périodicités (annuelle, trimestrielle…) |
| `niveaux_desagregation` | Dimensions de désagrégation |
| `niveaux_desagregation_valeurs` | Valeurs par niveau |
| `unites` | Unités de mesure |
| `dossiers` | Dossiers thématiques (Power BI, documents) |
| `users` | Utilisateurs avec 2FA TOTP |
| `audit_log` | Journal d'audit complet |
| `rate_limits` | Contrôle de débit API |
| `telechargements` | Statistiques de téléchargement |

### Workflow éditorial des observations

```
brouillon → soumis → valide → publie
                  ↘         ↘
                   rejete ←──┘
```

---

## Comptes par défaut

| Rôle | Email | Mot de passe | Notes |
|---|---|---|---|
| super_admin | admin@ins.ne | `Admin@2026!` | **Changer immédiatement** |

### Rôles disponibles

| Rôle | Permissions |
|---|---|
| `super_admin` | Tout, y compris gestion des utilisateurs et suppression |
| `admin` | CRUD données, validation, publication, import |
| `validateur` | Validation et publication des observations |
| `point_focal` | Saisie et soumission de ses propres données |

---

## API REST v1

Base URL : `/api/v1/`
Authentification : `X-Api-Key: <clé>` (header HTTP)
Format : JSON `utf8mb4`

### Endpoints

| Méthode | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/indicateurs` | Liste paginée des indicateurs |
| GET | `/api/v1/indicateurs/{slug}` | Détail d'un indicateur |
| GET | `/api/v1/indicateurs/{slug}/donnees` | Données publiées d'un indicateur |
| GET | `/api/v1/indicateurs/{slug}/export` | Export CSV ou JSON |
| GET | `/api/v1/thematiques` | Liste des thématiques |
| GET | `/api/v1/geo` | Entités géographiques |
| GET | `/api/v1/meta` | Métadonnées de la plateforme et statistiques |

### Paramètres de filtrage — `/donnees`

| Paramètre | Type | Description |
|---|---|---|
| `year_start` | int | Année de début (ex. `2015`) |
| `year_end` | int | Année de fin (ex. `2023`) |
| `geo_id` | int | ID de l'entité géographique |
| `niveau_id` | int | ID du niveau de désagrégation |
| `valeur` | string | Valeur de désagrégation |

### Exemple de réponse — `/api/v1/indicateurs`

```json
{
  "success": true,
  "data": [
    {
      "id": 7,
      "slug": "migrants-objets-trafic",
      "libelle_fr": "Migrants objets de trafic",
      "thematique": "Irrégularité et trafic",
      "unite_symbole": "Nb",
      "frequence_libelle": "Annuelle"
    }
  ],
  "meta": {
    "total": 43,
    "page": 1,
    "per_page": 20,
    "total_pages": 3,
    "generated": "2026-05-18T10:00:00+00:00",
    "source": "Institut National de la Statistique — Niger",
    "license": "Open Data Commons ODbL"
  }
}
```

---

## Flux éditorial

### Saisie des données

1. Un **point_focal** se connecte → saisit une observation → sauvegarde en `brouillon`
2. Quand prêt, il **soumet** → statut passe à `soumis`
3. Un **validateur** ou **admin** examine → **valide** ou **rejette** avec commentaire
4. Un **admin** ou **super_admin** **publie** → visible publiquement et via API

### Import en masse (CSV/XLSX)

1. Télécharger le template : `GET /admin/import/template`
2. Remplir le fichier (colonnes : `indicateur_slug`, `periode_debut`, `total`, …)
3. Uploader → Dry-run → prévisualisation des erreurs et lignes valides
4. Confirmer → insertion en base

---

## Sécurité

| Mécanisme | Implémentation |
|---|---|
| Hachage mots de passe | `password_hash()` BCRYPT coût 12 |
| CSRF | Token session 64 hex, `hash_equals()` |
| 2FA TOTP | QR code + codes 6 chiffres, auto-submit JS |
| Rate limiting | Table MySQL `rate_limits`, fenêtre glissante |
| Sessions | HttpOnly · Secure · SameSite=Lax · régénération 30 min |
| Injections SQL | 100 % PDO requêtes préparées |
| XSS | `htmlspecialchars()` sur tous les affichages |
| Upload | Vérification MIME, taille max 10 Mo |
| Audit | Chaque action sensible tracée dans `audit_log` |

---

## Structure des fichiers

```
webapp/
├── app/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── AuthController.php      # Login, logout, 2FA, reset mdp
│   │   │   ├── DashboardController.php # Tableau de bord
│   │   │   ├── DonneeController.php    # CRUD observations + workflow
│   │   │   ├── DossierController.php   # Gestion dossiers thématiques
│   │   │   ├── ImportController.php    # Import CSV/XLSX dry-run/commit
│   │   │   ├── IndicateurController.php# CRUD indicateurs
│   │   │   └── UserController.php      # Gestion utilisateurs
│   │   ├── Api/
│   │   │   └── ApiController.php       # REST API v1
│   │   ├── DossierController.php       # Pages publiques dossiers
│   │   ├── HomeController.php          # Accueil public
│   │   ├── IndicateurController.php    # Fiches indicateurs publiques
│   │   └── PageController.php          # Pages statiques (à-propos, contact…)
│   ├── Core/
│   │   ├── Auth.php                    # Authentification & permissions
│   │   ├── Config.php                  # Chargement .env
│   │   ├── Database.php                # Singleton PDO
│   │   ├── RateLimit.php               # Rate limiting MySQL
│   │   ├── Request.php                 # Abstraction $_GET/$_POST/$_FILES
│   │   ├── Response.php                # json(), redirect(), notFound()
│   │   ├── Router.php                  # Routeur Front Controller
│   │   ├── Session.php                 # Sessions sécurisées + CSRF
│   │   ├── View.php                    # Rendu vues avec layout
│   │   └── helpers.php                 # url(), esc(), csrf_field(), slugify()
│   ├── Models/
│   │   ├── Indicateur.php              # Requêtes indicateurs + observations
│   │   └── Observation.php             # CRUD observations + workflow
│   └── Views/
│       ├── admin/                      # ~15 vues d'administration
│       ├── layouts/                    # admin.php, public.php, admin_auth.php
│       └── public/                     # ~10 vues publiques
├── database/
│   ├── schema.sql                      # DDL — 19 tables InnoDB utf8mb4
│   ├── seed.sql                        # 43 indicateurs + données de référence
│   └── migrate_old_data.php            # Script migration MySQL 5.7 → 8
├── public/
│   ├── index.php                       # Front controller + routes
│   ├── css/                            # Styles personnalisés
│   ├── js/                             # Scripts publics
│   └── images/                         # Assets statiques
├── storage/
│   ├── uploads/                        # Documents uploadés (writable)
│   ├── imports/                        # Fichiers d'import temporaires
│   └── exports/                        # Exports générés
├── vendor/                             # Composer (PHPMailer, QR code)
├── composer.json
├── .env                                # Variables d'environnement (non versionné)
├── .env.example                        # Template .env
└── README.md
```

---

## Développement

```bash
# Serveur de développement
php -S 0.0.0.0:8080 -t public/

# Vérification syntaxe PHP (tous les fichiers)
find app/ public/ database/ -name "*.php" | xargs php -l 2>&1 | grep -v "No syntax"

# Test API rapide
curl http://localhost:8080/api/v1/meta | python3 -m json.tool
curl http://localhost:8080/api/v1/indicateurs | python3 -m json.tool

# Vérifier les migrations en base
mysql -u pndm_user -p pndm -e "
  SELECT COUNT(*) AS indicateurs FROM indicateurs;
  SELECT COUNT(*) AS observations FROM observations WHERE statut='publie';
  SELECT statut, COUNT(*) FROM observations GROUP BY statut;"
```

---

## Licence

Les données publiées sont sous licence **Open Data Commons ODbL**.
Le code source est propriété de l'**Institut National de la Statistique — République du Niger**.

---

*Développé pour l'INS Niger — 2026*
