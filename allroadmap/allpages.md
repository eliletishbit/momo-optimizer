# All Pages & Routes - MomoOpti

Ce document liste l'ensemble des pages/routes de l'application, avec leurs permissions, leurs middlewares, et leurs descriptions. Il sert de référence pour le développement.

---

## Légende des permissions

| Symbole | Signification |
|---------|---------------|
| 🔓 | Page publique (accessible sans authentification) |
| 🔐 | Page privée (authentification requise) |
| 💎 | Page premium (abonnement actif requis) |
| 👑 | Page admin (is_admin = true) |
| ⚡ | Page dynamique (avec contrôleur/logique métier) |
| 📄 | Page statique (simple vue Blade) |

---

## 1. Pages publiques (🔓)

| Route | Page | Type | Middleware | Description |
|-------|------|------|------------|-------------|
| `/` | `welcome.blade.php` | 📄 | guest | Page d'accueil avec présentation du service |
| `/calculator` | `calculator.blade.php` | ⚡ | guest | Calculateur d'optimisation des frais |
| `/pricing` | `pricing.blade.php` | 📄 | guest | Page des tarifs et plans d'abonnement |
| `/about` | `about.blade.php` | 📄 | guest | Page "À propos" |
| `/contact` | `contact.blade.php` | 📄 | guest | Page de contact |
| `/login` | `auth/login.blade.php` | 📄 | guest | Page de connexion (Breeze) |
| `/register` | `auth/register.blade.php` | 📄 | guest | Page d'inscription (Breeze) |
| `/forgot-password` | `auth/forgot-password.blade.php` | 📄 | guest | Mot de passe oublié (Breeze) |
| `/reset-password/{token}` | `auth/reset-password.blade.php` | 📄 | guest | Réinitialisation du mot de passe (Breeze) |
| `/verify-email` | `auth/verify-email.blade.php` | 📄 | guest | Vérification email (Breeze) |

---

## 2. Pages privées (🔐)

| Route | Page | Type | Middleware | Description |
|-------|------|------|------------|-------------|
| `/dashboard` | `dashboard.blade.php` | ⚡ | auth | Tableau de bord utilisateur |
| `/profile` | `profile.blade.php` | ⚡ | auth | Gestion du profil (nom, email, mot de passe) |
| `/history` | `history.blade.php` | ⚡ | auth | Historique des optimisations |
| `/settings` | `settings.blade.php` | ⚡ | auth | Paramètres : moyens de paiement préférés |
| `/calculator/result` | `calculator/result.blade.php` | ⚡ | auth | Résultat du calculateur (post-analyse) |
| `/notifications` | `notifications.blade.php` | 📄 | auth | Liste des notifications |

---

## 3. Pages premium (💎)

| Route | Page | Type | Middleware | Description |
|-------|------|------|------------|-------------|
| `/premium/advanced-calculator` | `premium/advanced-calculator.blade.php` | ⚡ | auth,subscription | Calculateur avancé (multi-réseaux) |
| `/premium/export` | `premium/export.blade.php` | 📄 | auth,subscription | Export des résultats en PDF/CSV |
| `/premium/alerts` | `premium/alerts.blade.php` | ⚡ | auth,subscription | Gestion des alertes de baisse de frais |
| `/premium/analytics` | `premium/analytics.blade.php` | ⚡ | auth,subscription | Statistiques avancées d'économies |

---

## 4. Pages admin (👑)

| Route | Page | Type | Middleware | Description |
|-------|------|------|------------|-------------|
| `/admin/dashboard` | `admin/dashboard.blade.php` | ⚡ | auth,admin | Tableau de bord administrateur |
| `/admin/users` | `admin/users/index.blade.php` | ⚡ | auth,admin | Gestion des utilisateurs (liste, filtres) |
| `/admin/users/{id}/edit` | `admin/users/edit.blade.php` | ⚡ | auth,admin | Modification d'un utilisateur |
| `/admin/fees/receipt` | `admin/fees/receipt.blade.php` | ⚡ | auth,admin | Gestion des frais de réception |
| `/admin/fees/sending` | `admin/fees/sending.blade.php` | ⚡ | auth,admin | Gestion des frais d'envoi |
| `/admin/methods` | `admin/methods/index.blade.php` | ⚡ | auth,admin | Gestion des moyens de paiement |
| `/admin/methods/create` | `admin/methods/create.blade.php` | ⚡ | auth,admin | Création d'un moyen de paiement |
| `/admin/methods/{id}/edit` | `admin/methods/edit.blade.php` | ⚡ | auth,admin | Modification d'un moyen de paiement |
| `/admin/subscriptions` | `admin/subscriptions/index.blade.php` | ⚡ | auth,admin | Gestion des abonnements |
| `/admin/statistics` | `admin/statistics.blade.php` | ⚡ | auth,admin | Statistiques globales |

---

## 5. Partials (composants réutilisables)

| Fichier | Description |
|---------|-------------|
| `components/navigation.blade.php` | Barre de navigation (différenciée selon l'état) |
| `components/footer.blade.php` | Pied de page |
| `components/header.blade.php` | En-tête de page |
| `components/breadcrumb.blade.php` | Fil d'Ariane |
| `components/alert.blade.php` | Messages d'alerte (succès, erreur) |
| `components/card.blade.php` | Carte générique |
| `components/button.blade.php` | Bouton réutilisable |
| `components/form/input.blade.php` | Champ de formulaire |
| `components/form/select.blade.php` | Sélecteur |
| `components/form/checkbox.blade.php` | Case à cocher |
| `components/ranking-table.blade.php` | Tableau de classement des frais |
| `components/method-badge.blade.php` | Badge d'un moyen de paiement |
| `components/loading-spinner.blade.php` | Indicateur de chargement |

---

## 📌 Middlewares à appliquer

| Route Group | Middlewares |
|-------------|-------------|
| **Routes publiques** | `web`, `guest` (pour login/register) |
| **Routes privées** | `web`, `auth` |
| **Routes premium** | `web`, `auth`, `subscription` |
| **Routes admin** | `web`, `auth`, `admin` |

---

## ✅ Checklist de validation

- [ ] Toutes les routes ont une page associée.
- [ ] Toutes les pages ont un titre (`@section('title')`).
- [ ] Toutes les pages privées héritent de `layouts/app.blade.php`.
- [ ] Toutes les pages publiques héritent de `layouts/guest.blade.php`.
- [ ] Les pages admin ont des permissions spécifiques.
- [ ] Les pages premium ont des permissions spécifiques.
- [ ] Les partials sont réutilisables et modulaires.