🚀 Étape 1 : Initialiser le projet Laravel
Ce que tu dois faire (manuellement)
bash
# 1. Créer le projet Laravel
composer create-project laravel/laravel momo-optimizer

# 2. Se placer dans le dossier
cd momo-optimizer

# 3. Installer les packages nécessaires
composer require livewire/livewire
composer require laravel/breeze --dev

# 4. Installer les dépendances frontend
npm install
npm install -D tailwindcss postcss autoprefixer @tailwindcss/forms @tailwindcss/typography
npm install alpinejs

# 5. Initialiser Tailwind CSS
npx tailwindcss init -p

# 6. Configurer le fichier .env
cp .env.example .env
php artisan key:generate
Prompt pour l'IA (après l'installation)


Prompt 1 – Structure du projet

text
Je viens d'installer un projet Laravel avec Livewire, Tailwind CSS, Alpine.js et Breeze.

Contexte : Je construis MomoOpti, un SaaS de comparaison et d'optimisation des frais de retrait Mobile Money (MTN, Moov, Celtiis) au Bénin.

Les fichiers de planification sont dans le dossier `allroadmap/` :
- `project-overview.md` : Vue d'ensemble du projet
- `guidelines.md` : Règles de développement et charte graphique
- `database-design.md` : Schéma de la base de données
- `features.md` : Liste des fonctionnalités
-`architecture.md` : architecture présumé
`stack-technologique.md` : liste des stacks à utiliser

Mes instructions :
1. Configure la structure de dossiers selon les guidelines (app/Services, app/Http/Livewire, etc.)
2. Configure l'authentification Breeze avec Blade et Tailwind
3. Prépare le fichier `resources/css/app.css` pour Tailwind
4. Configure Vite pour compiler les assets

Ne commence pas à coder les fonctionnalités pour l'instant, juste la structure de base.

/////////////////////////////////////////////////////////////////////////////////////



🔧 Étape 2 : Configurer Supabase
Ce que tu dois faire (manuellement)
Crée un compte sur Supabase

Crée un nouveau projet

Récupère les informations de connexion dans les paramètres de l'API

Prompt pour l'IA
Prompt 2 – Configuration Supabase

text
Je veux configurer mon projet Laravel pour utiliser Supabase (PostgreSQL) comme base de données.

Informations de connexion Supabase :
- Host : db.acvwzwjoikyjvpoalxdh.supabase.co
- Port : 5432
- Database : postgres
- Username : postgres
- Password : a+$3+9FZKu_XV?m
- SSL : Requis

Mes instructions :
1. Mets à jour le fichier `.env` avec les informations de connexion
2. Configure `config/database.php` pour utiliser PostgreSQL avec SSL
3. Teste la connexion et confirme-moi qu'elle fonctionne

Utilise les informations du fichier `allroadmap/database-design.md` pour comprendre la structure des tables.

////////////////////////////////////////////////////////////////////////////////////////////////////////


🗄️ Étape 3 : Créer les migrations et les modèles
Prompt pour l'IA
Prompt 3 – Migrations et Modèles

text

En te basant sur le contenu des fichiers `/allroadmap/project-overview.md`, `/allroadmap/database-summary.md `et surtout `/allroadmap/database-design.md`, crée les migrations et les modèles Eloquent pour toutes les tables de la base de donnée.

Pour chaque migration :
- Utilise UUID comme clé primaire (`$table->uuid('id')->primary()`)
- Ajoute les contraintes de clés étrangères
- Ajoute les index nécessaires

Pour chaque modèle :
- Définis les relations (belongsTo, hasMany, etc.)
- Ajoute les attributs `$fillable` ou `$guarded`
- Ajoute les casts pour les champs JSON et les dates

Exécute les migrations après la création.

Ensuite, crée des seeders pour :
- Les réseaux (MTN, Moov, Celtiis)
- Les grilles tarifaires de retrait (avec les données du fichier database-design.md)
- Les grilles tarifaires de transfert (si disponibles)

Donne-moi le code complet de chaque fichier.


////////////////////////////////////////////////////////////////////////////////////////////
Etape 3 - 2

je veux créer et configurer les middlewares essentiels pour mon application Laravel.

Contexte :
- Projet : MomoOpti (SaaS d'optimisation des frais Mobile Money)
- Framework : Laravel 11/12
- Authentification : Laravel Breeze (Blade)
- La table `users` a maintenant un champ `is_admin` qui indique si un utilisateur est administrateur.

Objectif :
Créer tous les middlewares nécessaires pour gérer :
1. L'accès administrateur (routes admin)
2. La vérification d'abonnement (routes premium)
3. La redirection après connexion/déconnexion
4. La vérification d'email (si non vérifié)

Mes instructions détaillées :

1. Crée le middleware `EnsureUserIsAdmin` (app/Http/Middleware/EnsureUserIsAdmin.php) :
   - Vérifie que l'utilisateur est connecté ET que `is_admin` est true
   - Si non, redirige vers la page d'accueil avec un message d'erreur
   - Ou retourne une erreur 403 (à toi de voir, je préfère une redirection)

2. Crée le middleware `CheckSubscription` (app/Http/Middleware/CheckSubscription.php) :
   - Vérifie que l'utilisateur connecté a un abonnement actif (Premium, Pro, Business)
   - Si non, redirige vers la page /pricing avec un message d'erreur
   - Les utilisateurs gratuits (free) n'ont pas accès aux fonctionnalités premium

3. Crée le middleware `RedirectIfAuthenticated` (si ce n'est pas déjà fait par Breeze) :
   - Vérifie que l'utilisateur n'est pas déjà connecté
   - Si connecté, redirige vers le dashboard

4. Crée le middleware `EnsureEmailIsVerified` (si ce n'est pas déjà fait par Breeze) :
   - Vérifie que l'utilisateur a vérifié son email
   - Si non, redirige vers la page de vérification

5. Enregistre tous ces middlewares dans `bootstrap/app.php` (Laravel 11) avec des alias :
   - 'admin' => EnsureUserIsAdmin::class
   - 'subscription' => CheckSubscription::class
   - 'verified' => EnsureEmailIsVerified::class (si déjà défini par Breeze, garde-le)

6. Donne-moi des exemples d'utilisation dans `routes/web.php` :
   - Groupe de routes admin protégé par `auth` + `admin`
   - Groupe de routes premium protégé par `auth` + `subscription`
   - Exemple de route publique (accueil)
   - Exemple de route privée (dashboard)

7. Ajoute des méthodes utilitaires dans le modèle User :
   - `isAdmin()` : retourne true si `is_admin` est true
   - `hasActiveSubscription()` : retourne true si l'abonnement est actif (premium, pro, business)
   - `canAccessPremium()` : combine les deux (ou autre logique)

Donne-moi le code complet de chaque fichier, avec des commentaires clairs en français.





///////////////////////////////////////////////////////////////////////////////////////////////////




Étape 4 : Création des routes
Prompt 4 – Routes (web.php)
Je dois créer toutes les routes de mon application MomoOpti.

**Contexte :**
- Framework : Laravel 11/12.
- Authentification : Laravel Breeze (Blade).
- Les middlewares disponibles : `auth`, `guest`, `admin` , `subscription` , `verified`.

**Référence :**
- Toutes les pages et routes sont listées dans le fichier `allroadmap/allpages.md`.
- Chaque page y est décrite avec son type (public, privé, premium, admin), son middleware, et sa description.

**Mes instructions :**
1. Ouvre le fichier `routes/web.php`.
2. En lisant le fichier `allroadmap/allpages.md`, crée TOUTES les routes listées.
3. Utilise `Route::view()` pour les pages statiques (`📄`).
4. Utilise `Route::get()` avec un contrôleur pour les pages dynamiques (`⚡`).
5. Applique les middlewares appropriés selon le fichier `allpages.md` :
   - Pages publiques : pas de middleware (sauf `guest` pour login/register).
   - Pages privées : `middleware(['auth'])`.
   - Pages premium : `middleware(['auth', 'subscription'])`.
   - Pages admin : `middleware(['auth', 'admin'])` et préfixe `/admin`.
6. Ajoute les routes d'authentification Breeze (déjà incluses par `require __DIR__.'/auth.php'`).
7. Organise les routes par groupes logiques (public, privé, premium, admin).
8. Donne-moi le code COMPLET du fichier `routes/web.php` avec des commentaires clairs en français.

**IMPORTANT :** Ne zappe AUCUNE route listée dans `allpages.md`. Toutes doivent être présentes.



///////////////////////////////////////////////////////////////////////////////////////////////////
etape 5
Je dois créer tous les contrôleurs nécessaires pour mon application MomoOpti.

**Contexte :**
- Framework : Laravel 11/12.
- Modèles : User, Method, UserMethod, ReceiptFee, SendingFee, Subscription, OptimizationHistory, Country.
- Les vues sont organisées selon `allroadmap/allpages.md`.

**Référence :**
- Les pages dynamiques (`⚡`) listées dans `allroadmap/allpages.md` nécessitent un contrôleur.
- Chaque page dynamique doit avoir une méthode dans un contrôleur.

**Mes instructions :**
1. Crée les contrôleurs suivants dans `app/Http/Controllers/` :

   **Contrôleurs publics :**
   - `PageController` : pages statiques (home, about, contact, pricing)
     - `home()` → retourne `welcome`
     - `about()` → retourne `pages.about`
     - `contact()` → retourne `pages.contact`
     - `pricing()` → retourne `pages.pricing`

   - `CalculatorController` : calculateur d'optimisation
     - `index()` → affiche le formulaire (`pages.calculator`)
     - `result()` → traite le montant et affiche les résultats (`pages.calculator.result`)

   **Contrôleurs privés :**
   - `DashboardController` : tableau de bord
     - `index()` → affiche le dashboard (`pages.dashboard`)

   - `ProfileController` : gestion du profil
     - `edit()` → affiche le formulaire (`pages.profile`)
     - `update()` → met à jour le profil (nom, email, mot de passe)

   - `HistoryController` : historique des optimisations
     - `index()` → affiche la liste (`pages.history`)

   - `SettingsController` : paramètres (moyens de paiement)
     - `index()` → affiche les paramètres (`pages.settings`)
     - `store()` → ajoute un moyen de paiement
     - `update()` → modifie un moyen de paiement
     - `destroy()` → supprime un moyen de paiement

   - `NotificationController` : gestion des notifications
     - `index()` → affiche les notifications (`pages.notifications`)
     - `markAsRead()` → marque une notification comme lue

   **Contrôleurs premium :**
   - `PremiumController` : fonctionnalités payantes
     - `advancedCalculator()` → calculateur avancé (`pages.premium.advanced-calculator`)
     - `export()` → export des résultats (`pages.premium.export`)
     - `alerts()` → gestion des alertes (`pages.premium.alerts`)
     - `analytics()` → statistiques avancées (`pages.premium.analytics`)

   **Contrôleurs admin (dans `Admin/`) :**
   - `Admin/DashboardController` : tableau de bord admin
     - `index()` → affiche le dashboard admin (`pages.admin.dashboard`)

   - `Admin/UserController` : gestion des utilisateurs
     - `index()` → liste des utilisateurs (`pages.admin.users.index`)
     - `edit()` → formulaire d'édition (`pages.admin.users.edit`)
     - `update()` → met à jour un utilisateur
     - `destroy()` → supprime un utilisateur

   - `Admin/FeeController` : gestion des frais
     - `receipt()` → gestion des frais de réception (`pages.admin.fees.receipt`)
     - `sending()` → gestion des frais d'envoi (`pages.admin.fees.sending`)
     - `store()` → ajoute un palier
     - `update()` → modifie un palier
     - `destroy()` → supprime un palier

   - `Admin/MethodController` : gestion des moyens de paiement
     - `index()` → liste des moyens (`pages.admin.methods.index`)
     - `create()` → formulaire de création (`pages.admin.methods.create`)
     - `store()` → enregistre un nouveau moyen
     - `edit()` → formulaire d'édition (`pages.admin.methods.edit`)
     - `update()` → met à jour un moyen
     - `destroy()` → supprime un moyen

   - `Admin/SubscriptionController` : gestion des abonnements
     - `index()` → liste des abonnements (`pages.admin.subscriptions.index`)
     - `update()` → modifie un abonnement
     - `destroy()` → annule un abonnement

   - `Admin/StatisticsController` : statistiques globales
     - `index()` → affiche les statistiques (`pages.admin.statistics`)

2. Pour chaque contrôleur, ajoute les méthodes nécessaires avec leur logique métier :
   - Utilise les modèles Eloquent pour interagir avec la base de données.
   - Utilise les validateurs Laravel pour les requêtes.
   - Utilise `session()->flash()` pour les messages de succès/erreur.
   - Redirige vers les routes appropriées après chaque action.

3. Organise les contrôleurs dans des dossiers logiques (`Admin/` pour les admins, `Premium/` pour les premium, etc.).

4. Donne-moi le code COMPLET de chaque contrôleur avec des commentaires clairs en français.

**IMPORTANT :** Chaque page dynamique listée dans `allpages.md` doit avoir une méthode correspondante dans un contrôleur.

/////////////////////////////////////////////////////////////////////////////////////////
etape6

en tenant compte de toutes les routes, créer toutes les vues Blade de mon application MomoOpti.

**Contexte :**
- Framework : Laravel 11/12.
- Layouts : `layouts/app.blade.php` (privé) et `layouts/guest.blade.php` (public).
- Les pages sont listées dans `allroadmap/allpages.md` et les lignes directives pour le design des pages sont dans
/allroadmap/guidelines.md
- n'oublie pas de tenir compte du nom des variables et colonnes definis dans les modèles et controlleurs dans les différentes pages/vues afin de recuperer les vraie données et eviter toute erreur.

**Référence :**
- Chaque page (même statique) doit avoir un fichier Blade.
- Les layouts et les partials sont définis dans `allpages.md`.

**Mes instructions :**

1. **Crée les layouts :**
   - `resources/views/layouts/app.blade.php` : layout pour les pages privées.
     - Inclut : DOCTYPE, head (titre, métas, @vite, @livewireStyles), body, navigation (header), contenu (@yield('content')), footer, @livewireScripts.
     - La navigation doit afficher : logo, liens (Dashboard, Calculatrice, Historique, Tarifs, Profil, Paramètres, Déconnexion).
   - `resources/views/layouts/guest.blade.php` : layout pour les pages publiques.
     - Similaire au layout `app` mais sans la navigation (juste un logo et le contenu centré).

2. **Crée les pages publiques (🔓) dans `resources/views/pages/` :**
   - `home.blade.php` : page d'accueil avec présentation du service, avantages, CTA.
   - `calculator.blade.php` : page du calculateur avec le formulaire (montant, type d'opération).
   - `pricing.blade.php` : page des tarifs avec les plans (gratuit, premium, pro, business).
   - `about.blade.php` : page "À propos" (contenu statique).
   - `contact.blade.php` : page de contact (formulaire simple).

3. **Crée les pages privées (🔐) dans `resources/views/pages/` :**
   - `dashboard.blade.php` : tableau de bord (statistiques personnelles).
   - `profile.blade.php` : formulaire de modification du profil (nom, email, mot de passe).
   - `history.blade.php` : liste des optimisations passées avec pagination.
   - `settings.blade.php` : gestion des moyens de paiement préférés (ajout, suppression).
   - `notifications.blade.php` : liste des notifications.

4. **Crée les pages premium (💎) dans `resources/views/pages/premium/` :**
   - `advanced-calculator.blade.php` : calculateur avancé (multi-réseaux, combinaisons).
   - `export.blade.php` : export des résultats en PDF/CSV.
   - `alerts.blade.php` : gestion des alertes de baisse de frais.
   - `analytics.blade.php` : statistiques avancées d'économies.

5. **Crée les pages admin (👑) dans `resources/views/pages/admin/` :**
   - `dashboard.blade.php` : tableau de bord admin (statistiques globales).
   - `users/index.blade.php` : liste des utilisateurs avec filtres et actions.
   - `users/edit.blade.php` : formulaire d'édition d'un utilisateur.
   - `fees/receipt.blade.php` : gestion des frais de réception (CRUD).
   - `fees/sending.blade.php` : gestion des frais d'envoi (CRUD).
   - `methods/index.blade.php` : liste des moyens de paiement.
   - `methods/create.blade.php` : formulaire de création d'un moyen.
   - `methods/edit.blade.php` : formulaire d'édition d'un moyen.
   - `subscriptions/index.blade.php` : liste des abonnements.
   - `statistics.blade.php` : statistiques globales (graphiques, KPIs).

6. **Crée les partials (composants réutilisables) dans `resources/views/components/` :**
   - `navigation.blade.php` : barre de navigation (liens différenciés selon l'état).
   - `footer.blade.php` : pied de page.
   - `alert.blade.php` : messages d'alerte (succès, erreur).
   - `card.blade.php` : carte générique.
   - `button.blade.php` : bouton réutilisable.
   - `form/input.blade.php` : champ de formulaire.
   - `form/select.blade.php` : sélecteur.
   - `ranking-table.blade.php` : tableau de classement des frais.
   - `method-badge.blade.php` : badge d'un moyen de paiement.
   - `loading-spinner.blade.php` : indicateur de chargement.

7. Pour chaque page (publique, privée, premium, admin), elle doit :
   - Hériter du bon layout (`@extends('layouts.app')` ou `@extends('layouts.guest')`).
   - Définir le titre (`@section('title')`).
   - Définir le contenu (`@section('content')`).
   - Utiliser les partials pour les éléments récurrents.

8. Donne-moi le code COMPLET de chaque layout, chaque page, et chaque partial.

**IMPORTANT :** Aucune page listée dans `allpages.md` ne doit manquer. Toutes doivent être générées.


/////////////////////////////////////////////////////////////////////////////////////////////////////
**************************************************************************************************************
🧮 Étape 7: Créer l'algorithme d'optimisation
Prompt pour l'IA
Prompt 4 – Algorithme d'optimisation des frais

text
Je dois créer un service d'optimisation des frais de retrait. Le but est de trouver la combinaison de retraits (sur un ou plusieurs réseaux) qui minimise les frais pour un montant donné.

Contexte :
- L'utilisateur entre un montant (ex: 175 000 FCFA)
- Les réseaux disponibles : MTN, Moov, Celtiis etc (avec leurs paliers et frais)
- L'algorithme doit explorer toutes les combinaisons possibles (1, 2 ou 3 retraits)
- Les paliers doivent être respectés pour chaque retrait
- Retourner la combinaison gagnante avec le détail des montants par réseau

Structure du service :
- Crée `app/Services/FeeOptimizer.php`
- Méthode : `public function optimizeWithdrawal(float $amount): array`
- La méthode retourne : le meilleur réseau, la combinaison (JSON), les frais totaux, les économies réalisées

Contraintes techniques :
- Utilise les données de la table `receipt_fees`
- Cache les résultats pour les montants fréquemment demandés (Redis ou cache Laravel)
- Gère les cas où le montant est trop petit (moins de 100 FCFA)

Teste l'algorithme avec 175 000 FCFA et vérifie que :
- Moov seul : 1 750 FCFA (le moins cher)
- MTN seul : 2 000 FCFA
- Celtiis seul : 2 000 FCFA
-mtn fractionné : 100 000 + 75000
-moov fractioné
-etc
-autres proposition d'option de retrait combinés possibles avec des taux ou les frais sont interessantes (exemple: mtn+moov, celtis+mtn)

Donne-moi le code complet du service, ainsi que les tests unitaires associés.



/////////////////////////////////////////////////////////////////////////////////////////



🎨 Étape 8 : Créer l'interface utilisateur (Livewire)
Prompt pour l'IA
Prompt 5 – Interface de calcul (Livewire)

text
Je veux créer l'interface principale de l'application : un composant Livewire pour le calcul et la comparaison des frais de retrait.

Contexte : Suivre la charte graphique définie dans `docs/roadmap/guidelines.md`.

Fonctionnalités du composant `Calculator` (dans `app/Http/Livewire/Calculator.php`) :
1. Un champ de saisie pour le montant (en FCFA)
2. Une sélection du type d'opération (Retrait / Envoi)
3. Un bouton "Calculer"
4. Affichage des résultats :
   - Tableau comparatif des frais par réseau (MTN, Moov, Celtiis)
   - Mise en évidence du réseau le moins cher (badge "Meilleur")
   - Option optimale avec la combinaison de retraits
   - Affichage des économies réalisées
   - Montant net reçu par le destinataire

Comportement :
- Utiliser `wire:model.live` pour le champ montant (calcul en temps réel)
- Afficher un indicateur de chargement pendant le calcul
- Gérer les erreurs (montant invalide, montant trop élevé)

Interface utilisateur (vues Livewire) :
- Vue : `resources/views/livewire/calculator.blade.php`
- Utiliser Tailwind CSS pour le style
- Respecter les couleurs définies dans les guidelines

Donne-moi le code complet du composant Livewire, de la vue, et intègre-le dans la page d'accueil.



////////////////////////////////////////////////////////////////////////////////////////////


💳 Étape 9 : Intégrer FedaPay
Prompt pour l'IA
Prompt 6 – Paiements avec FedaPay

text
J'ai besoin d'intégrer FedaPay pour les paiements d'abonnement.

Contexte :
- Plans : Premium (1500 FCFA/mois), Pro (5000 FCFA/mois), Business (10000 FCFA/mois)
- Moyens de paiement : Mobile Money (MTN, Moov), Cartes
- Essai gratuit de 7 jours pour tous les plans

Structure :
1. Créer un service `app/Services/FedaPayService.php` avec :
   - Méthode pour créer une transaction
   - Méthode pour vérifier le statut d'un paiement
   - Méthode pour gérer les webhooks

2. Créer une page `/pricing` avec les plans et les boutons de souscription

3. Créer le composant Livewire pour la souscription

4. Gérer les webhooks FedaPay pour activer automatiquement les abonnements

Documentation FedaPay : https://docs.fedapay.com/

Donne-moi le code complet du service, du contrôleur, du composant Livewire, et des routes.



///////////////////////////////////////////////////////////////////////////////////////////




📱 Étape 10 : Configurer la PWA
Prompt pour l'IA
Prompt 7 – Progressive Web App

text
Je veux transformer mon application Laravel en PWA installable sur mobile.

Fonctionnalités PWA :
1. Fichier `manifest.json` avec :
   - Nom de l'application : "MomoOpti"
   - Icônes (taille 192x192, 512x512)
   - Couleur de thème : #4F46E5 (indigo)
   - Affichage : fullscreen

2. Service Worker pour le cache :
   - Mettre en cache les pages principales (accueil, calcul, etc.)
   - Mode "offline" partiel (afficher un message si l'utilisateur est hors ligne)

3. Bannière d'installation (pour Chrome Android)

4. Utiliser le package `laravel-pwa` ou faire une configuration manuelle

Donne-moi le code pour :
- Le fichier `manifest.json`
- Le service worker (`public/sw.js`)
- L'intégration dans le layout Blade
- La configuration du package (si utilisé)



/////////////////////////////////////////////////////////////////////////////////////////////



🧪 Étape 11 : Tests et déploiement
Prompt pour l'IA
Prompt 8 – Tests et préparation au déploiement

text
Je veux préparer mon application pour le déploiement en production.

Tâches à effectuer :
1. Écrire des tests unitaires et fonctionnels pour :
   - L'algorithme d'optimisation (FeeOptimizer)
   - Les composants Livewire (Calculator)
   - L'intégration FedaPay (paiements)
   - Les modèles et leurs relations

2. Configurer l'environnement de production :
   - Variables d'environnement (.env.production)
   - Optimisation Laravel (php artisan optimize)
   - Configuration du cache (config/cache.php)

3. Préparer le déploiement sur Vercel ou DigitalOcean
   - Fichier `vercel.json` ou `Dockerfile`
   - Scripts de déploiement

4. Mettre à jour la documentation :
   - README.md avec les instructions d'installation et de déploiement

Donne-moi les commandes et les fichiers de configuration nécessaires.
✅ Résumé des prompts pour l'IA
Prompt	Objectif	Fichiers cibles
1	Structure du projet	composer.json, webpack.mix.js, resources/views/
2	Configuration Supabase	.env, config/database.php
3	Migrations et modèles	database/migrations/, app/Models/
4	Algorithme d'optimisation	app/Services/FeeOptimizer.php
5	Interface Livewire	app/Http/Livewire/, resources/views/livewire/
6	Paiements FedaPay	app/Services/FedaPayService.php, app/Http/Controllers/
7	PWA	manifest.json, sw.js, resources/views/layouts/
8	Tests et déploiement	tests/, README.md, Dockerfile
