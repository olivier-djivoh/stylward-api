# Stylward — API

Plateforme SaaS multi-tenant permettant à chaque styliste, tailleur ou créateur de mode de créer sa **boutique numérique**, gérer son **catalogue de modèles** et **partager un lien public** avec ses clients.

---

## 🎯 Fonctionnalités du MVP

### Pour l'artisan de la mode (authentifié)

- Inscription / Connexion via API (Sanctum)
- Création de sa boutique avec **slug unique** auto-généré
- Ajout / Modification / Suppression de modèles
- Publication et dépublication de modèles
- Upload d'**images sécurisées** (UUID, validation MIME, taille max 5 Mo)
- Catégorisation des modèles (Vestes, Costumes, Robes, etc.)

### Pour le client (public, sans compte)

- Consultation d'une boutique via son lien unique : `/api/v1/public/shops/{slug}`
- Catalogue filtré : **uniquement** les modèles `published`
- Détail d'un modèle avec images
- Contact via WhatsApp (à venir côté frontend)

### Sécurité et isolation

- **UUID** partout (aucun ID devinable)
- **Isolation multi-tenant** stricte (Global Scope + Policies)
- **UUID sur les tokens** Sanctum
- Validation stricte (12 caractères min, majuscule, minuscule, chiffre, caractère spécial)
- **Rate limiting** : 5/min sur auth, 60/min sur public, 120/min sur API authentifiée
- Headers de sécurité (X-Frame-Options, X-Content-Type-Options, etc.)

---

## 🏗️ Stack technique

| Couche          | Technologie                  |
| --------------- | ---------------------------- |
| Framework       | Laravel 12                   |
| PHP             | 8.5                          |
| Base de données | PostgreSQL 18                |
| Auth API        | Laravel Sanctum              |
| Frontend        | (à venir) React + Vite       |
| Environnement   | Windows natif (Docker prévu) |

---

## 📦 Installation

### Prérequis

- PHP 8.4+ avec extensions : `pdo_pgsql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `zip`, `intl`, `gd`
- Composer 2
- PostgreSQL 16+
- Git

### Étapes

```bash
# 1. Cloner le projet
git clone <url-du-repo> stylward-api
cd stylward-api

# 2. Installer les dépendances PHP
composer install

# 3. Copier le fichier d'environnement
copy .env.example .env

# 4. Générer la clé d'application
php artisan key:generate

# 5. Configurer la base de données dans .env
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=saas_stylistes
# DB_USERNAME=saas_user
# DB_PASSWORD=votre_mot_de_passe

# 6. Créer la base dans PostgreSQL
# psql -U postgres -c "CREATE DATABASE saas_stylistes;"
# psql -U postgres -c "CREATE USER saas_user WITH PASSWORD '...';"
# psql -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE saas_stylistes TO saas_user;"

# 7. Lancer les migrations + seeder de démo
php artisan migrate:fresh --seed

# 8. Démarrer le serveur
php artisan serve
```
