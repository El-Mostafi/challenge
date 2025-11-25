# Gestion de Notes de Frais - Challenge Laravel

Application Laravel de gestion de notes de frais avec workflow d'approbation.

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate

# Configurer DB dans .env puis :
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

## Comptes de test

| Rôle      | Email                 | Mot de passe |
| --------- | --------------------- | ------------ |
| Manager   | manager1@example.com  | manager1     |
| Employé 1 | employee1@example.com | employee1    |
| Employé 2 | employee2@example.com | employee2    |

## API Endpoints

| Méthode | Endpoint                   | Accès   |
| ------- | -------------------------- | ------- |
| POST    | /api/login                 | Public  |
| GET     | /api/expenses              | Tous    |
| POST    | /api/expenses              | Employé |
| PUT     | /api/expenses/{id}         | Employé |
| POST    | /api/expenses/{id}/submit  | Employé |
| POST    | /api/expenses/{id}/approve | Manager |
| POST    | /api/expenses/{id}/reject  | Manager |
| POST    | /api/expenses/{id}/pay     | Manager |
| GET     | /api/stats/summary         | Manager |
| POST    | /api/exports/expenses      | Manager |

## Tests

```bash
php artisan test
```

14 tests, 32 assertions - Tous réussis ✅

## Points techniques

-   Laravel 10.x
-   Authentification Sanctum + Session
-   Policies pour la sécurité
-   FormRequest pour validation
-   Cache 60s sur statistiques
-   Job Laravel pour export CSV
-   Tests Feature
-   Code commenté
-   Frontend Blade
-   Historique des changements (expense_logs)

## Workflow

```
DRAFT → SUBMITTED → APPROVED → PAID
           ↓
        REJECTED
```
