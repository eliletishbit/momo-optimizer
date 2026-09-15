# Guidelines - MomoOpti

Ce document définit les règles de développement, les choix technologiques, les conventions de code et les directives de design pour le projet MomoOpti, alignées sur la nouvelle vision centrée sur l'utilisateur.

---

## 🧱 Stack Technologique

| Couche | Technologie | Version | Justification |
|--------|-------------|---------|---------------|
| **Framework** | Laravel | 11/12 | Framework PHP robuste, productif, avec écosystème riche |
| **Frontend (UI)** | Blade + Tailwind CSS | 3.x | Rendu SSR natif, excellent pour le SEO, rapidité de développement |
| **Interactivité** | Livewire 3 + Alpine.js 3 | 3.x | Interactivité asynchrone sans JavaScript complexe |
| **Base de données** | PostgreSQL (via Supabase) | 15+ | SGBD relationnel performant, managé, avec plan Free généreux |
| **PWA** | Configuration Laravel PWA | - | Transformation de l'app en PWA installable |
| **Paiements** | FedaPay | - | Mobile Money, cartes, local (Afrique de l'Ouest) |
| **Déploiement** | Vercel / DigitalOcean | - | Déploiement automatisé et scalable |
| **Versioning** | Git + GitHub | - | Gestion de versions collaborative |

### Dépendances principales

```json
"require": {
    "php": "^8.2",
    "laravel/framework": "^11.0|^12.0",
    "livewire/livewire": "^3.0",
    "laravel/breeze": "^2.0",
    "feda-pay/laravel": "^1.0"
},
"require-dev": {
    "laravel/sail": "^1.26",
    "laravel/pint": "^1.13",
    "nunomaduro/collision": "^8.0"
},
"devDependencies": {
    "@tailwindcss/forms": "^0.5.7",
    "@tailwindcss/typography": "^0.5.10",
    "alpinejs": "^3.13.0",
    "tailwindcss": "^3.4.0",
    "vite": "^5.0.0"
}
🎨 Charte Graphique (UI/UX)
Palette de couleurs
Usage	Code Hex	Exemple
Primaire	#4F46E5 (Indigo 600)	Boutons principaux, liens, accents
Primaire foncé	#4338CA (Indigo 700)	Hover, états actifs
Secondaire (succès)	#059669 (Emeraude 600)	Économies, options recommandées
Alerte (danger)	#DC2626 (Rouge 600)	Frais élevés, erreurs
Avertissement	#D97706 (Ambre 600)	Attention, seuils
Fond principal	#F9FAFB (Gris 50)	Arrière-plan des pages
Fond secondaire	#FFFFFF (Blanc)	Cartes, panneaux
Texte principal	#111827 (Gris 900)	Titres, textes importants
Texte secondaire	#6B7280 (Gris 500)	Descriptions, sous-titres
Bordure	#E5E7EB (Gris 200)	Séparateurs, contours
Typographie
Élément	Police	Taille	Poids
Titre principal (h1)	Inter (sans-serif)	2.25rem (36px)	700 (bold)
Titre secondaire (h2)	Inter	1.875rem (30px)	600 (semibold)
Titre tertiaire (h3)	Inter	1.5rem (24px)	600
Texte normal	Inter	1rem (16px)	400
Texte petit	Inter	0.875rem (14px)	400
Texte très petit	Inter	0.75rem (12px)	400
Chiffres et montants	Inter (tabulaire)	1.25rem (20px)	700
Police utilisée : Inter (importée via Google Fonts)

Composants et styles
Élément	Style Tailwind	Notes
Bouton principal	bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-xl shadow-md hover:shadow-lg transition-all	Arrondi (xl), ombre légère
Bouton secondaire	bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3 px-6 rounded-xl border border-gray-300 transition-all	Contour gris
Bouton de validation	bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-6 rounded-xl	Vert pour actions positives
Bouton "Meilleure option"	bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold py-2 px-4 rounded-full border border-emerald-200	Badge pour l'option recommandée
Carte (card)	bg-white rounded-2xl shadow-lg p-6 border border-gray-100	Ombre douce, bords arrondis
Champ de saisie	w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all	Effet de focus indigo
Sélecteur (dropdown)	Même style que champ de saisie	-
Badge réseau	inline-flex items-center px-3 py-1 rounded-full text-sm font-medium	MTN (jaune), Moov (bleu), Celtiis (vert)
Résultat d'optimisation	bg-indigo-50 border border-indigo-200 rounded-xl p-4	Fond indigo très clair
Classement	border-b border-gray-100 py-3 px-4	Avec icône de médailles (🥇🥈🥉)
Icônes
Utiliser Heroicons (gratuit, compatible Tailwind) : https://heroicons.com/

Préférer les icônes solid pour les actions principales, outline pour les éléments secondaires

Taille standard : 20x20px (ou 24x24px pour les icônes majeures)

Responsive et Accessibilité
Mobile-first : Toute conception commence par l'écran mobile (< 640px)

Points de rupture : sm (640px), md (768px), lg (1024px), xl (1280px)

Contraste : Respecter un rapport de contraste d'au moins 4.5:1 pour le texte

Taille des cibles tactiles : Au moins 44x44px pour les boutons et liens

ARIA : Utiliser les attributs ARIA lorsque nécessaire (ex: aria-label)

🏗️ Organisation du Code
Structure des dossiers (Laravel)
text
app/
├── Http/
│   ├── Controllers/          # Contrôleurs traditionnels
│   ├── Livewire/             # ⚠️ ATTENTION : Ne pas utiliser ce dossier
│   └── Middleware/
├── Livewire/                  # ✅ NOUVEAU DOSSIER (Livewire 3)
│   ├── Calculator/
│   │   ├── Receipt.php       # Mode "Je reçois"
│   │   └── Send.php          # Mode "J'envoie"
│   ├── Settings/
│   │   └── Methods.php       # Gestion des moyens préférés
│   └── History.php           # Historique des transactions
├── Models/
│   ├── User.php
│   ├── Method.php
│   ├── UserMethod.php
│   ├── ReceiptFee.php
│   ├── SendingFee.php
│   ├── Subscription.php
│   ├── OptimizationHistory.php
│   └── Country.php           # Pour l'extension internationale
└── Services/
    ├── FeeOptimizer.php      # Algorithme d'optimisation personnalisé
    ├── CountryManager.php    # Gestion des pays et devises
    └── FedaPayService.php    # Intégration FedaPay

resources/
├── views/
│   ├── livewire/             # ✅ NOUVEAU DOSSIER (Livewire 3)
│   │   ├── calculator/
│   │   │   ├── receipt.blade.php
│   │   │   └── send.blade.php
│   │   ├── settings/
│   │   │   └── methods.blade.php
│   │   └── history.blade.php
│   ├── components/           # Composants Blade réutilisables
│   │   ├── layout/
│   │   │   ├── app.blade.php
│   │   │   └── guest.blade.php
│   │   ├── cards/
│   │   │   ├── option.blade.php
│   │   │   └── ranking.blade.php
│   │   └── buttons/
│   │       ├── primary.blade.php
│   │       └── secondary.blade.php
│   └── pages/                # Pages complètes
│       ├── dashboard.blade.php
│       ├── calculator.blade.php
│       └── settings.blade.php
├── css/
│   └── app.css              # Tailwind CSS
└── js/
    └── app.js               # Alpine.js

routes/
├── web.php                   # Routes principales
└── api.php                   # Routes API (si nécessaire)

docs/                         # Documentation du projet
├── project-overview.md
├── guidelines.md             # Ce fichier
├── database-design.md
├── features.md
└── roadmap.md
Conventions de nommage
Élément	Conventions	Exemple
Modèles	Singulier, PascalCase	Method, UserMethod, ReceiptFee
Tables	Pluriel, snake_case	methods, user_methods, receipt_fees
Contrôleurs	Singulier, PascalCase + "Controller"	MethodController
Composants Livewire	PascalCase	Receipt, Send, History
Vues Livewire	snake_case (dans livewire/)	calculator/receipt.blade.php
Vues pages	snake_case (dans pages/)	dashboard.blade.php
Routes	kebab-case	/optimize-receipt, /preferred-methods
Variables	camelCase	$totalFee, $bestOption, $userMethods
Constantes	UPPER_SNAKE_CASE	MAX_RETRY_ATTEMPTS
Méthodes	camelCase	optimizeReceipt(), getUserMethods()
Règles de codage
PHP : Suivre PSR-12 (norme Laravel par défaut)

Blade : Utiliser {{ }} pour l'échappement, {!! !!} seulement si nécessaire

Livewire : Utiliser les propriétés publiques, les méthodes d'action avec #[On] pour les événements

CSS : Utiliser Tailwind dans les classes HTML, ne créer des classes CSS personnalisées que pour des cas complexes

JavaScript : Utiliser Alpine.js pour l'interactivité légère, Livewire pour les interactions serveur

Gestion des erreurs
Toutes les exceptions doivent être loguées dans storage/logs/laravel.log

Les erreurs utilisateur doivent être affichées via les sessions (withErrors() ou session()->flash())

Utiliser les validateurs de Laravel pour les entrées utilisateur

🔐 Sécurité
Authentification : Laravel Breeze (Blade) — simple et efficace

Autorisation : Gates et Policies Laravel

Validation : Valider toutes les entrées utilisateur (utilisation de validate() ou Form Requests)

XSS : Utiliser l'échappement Blade ({{ }}) par défaut

CSRF : Protégé automatiquement par Laravel

CORS : Configurer si nécessaire pour l'API

Gestion des données sensibles
Les données des utilisateurs (comptes, numéros) sont stockées dans user_methods.account_id (chiffré si nécessaire)

Les mots de passe sont hachés avec bcrypt() (par défaut Laravel)

Les clés API (FedaPay, Supabase) sont stockées dans .env (jamais dans le code)

🌍 Internationalisation (i18n)
Préparation pour l'extension multi-pays
Langues : Préparer les fichiers de traduction dans resources/lang/ (ex: fr/, en/, pt/)

Devises : Utiliser le champ currency dans methods et countries ; utiliser money_format ou un package comme laravel-money

Pays : Utiliser la table countries pour gérer les pays supportés

Frais : Les grilles tarifaires sont liées à un pays (country_code)

Exemple de configuration
php
// config/app.php
'locale' => 'fr',
'fallback_locale' => 'fr',

// Dans le contrôleur
App::setLocale($user->preferred_locale ?? 'fr');

// Dans les vues
{{ __('messages.welcome') }}
📊 Logique Métier : Algorithme d'Optimisation
Classe FeeOptimizer
Emplacement : app/Services/FeeOptimizer.php

Responsabilité : Calculer et comparer les frais pour un montant donné, pour un type d'opération donné (receipt/send), pour les méthodes enregistrées par l'utilisateur.

Méthodes principales :

php
class FeeOptimizer
{
    public function optimizeReceipt($amount, $userMethods): array;
    public function optimizeSend($amount, $userMethods): array;
    private function calculateFeeForMethod($method, $amount): float;
    private function generateRanking($options): array;
    private function generateCombinedOption($options): ?array;
}
Structure de retour :

php
[
    'ranking' => [
        ['method' => 'mtn', 'fee' => 2000, 'rank' => 2],
        ['method' => 'moov', 'fee' => 1750, 'rank' => 1],
        ['method' => 'celtiis', 'fee' => 2000, 'rank' => 2],
    ],
    'best' => ['method' => 'moov', 'fee' => 1750],
    'combined' => null, // ou ['options' => [...], 'fee' => 1900]
    'savings' => 250,
    'net_amount' => 173250,
]
🧪 Tests
Types de tests à écrire
Type	Emplacement	Couverture minimale
Unitaires	tests/Unit/	80% des services (FeeOptimizer, etc.)
Fonctionnels	tests/Feature/	70% des routes et composants Livewire
Intégration	tests/Integration/	60% des interactions base de données
Commande de test
bash
php artisan test
📦 Workflow de développement
1. Planification
Mettre à jour les fichiers docs/ si nécessaire

Valider la fonctionnalité avec le chef de projet

2. Développement
Créer une branche (git checkout -b feature/nom-feature)

Suivre l'ordre : Migration → Modèle → Service → Contrôleur → Vue

Écrire les tests

3. Revue
Auto-revue du code

Demander une revue à un pair (si disponible)

4. Test
Lancer les tests (php artisan test)

Tester manuellement sur l'environnement local

5. Intégration
git add .

git commit -m "feat: description"

git push origin feature/nom-feature

Créer une Pull Request sur GitHub

6. Déploiement
Fusionner la PR sur main

Déployer sur staging pour validation

Déployer sur production (après validation)

🔧 Commandes utiles
Commande	Description
php artisan serve	Lancer le serveur de développement
npm run dev	Compiler les assets (Vite) en mode développement
npm run build	Compiler les assets pour la production
php artisan make:livewire Calculator/Receipt	Créer un composant Livewire (manuellement)
php artisan make:migration create_methods_table	Créer une migration
php artisan migrate	Exécuter les migrations
php artisan db:seed	Remplir les tables avec des données de test
php artisan test	Exécuter les tests
php artisan optimize:clear	Nettoyer le cache
Dernière mise à jour : 13 août 2026