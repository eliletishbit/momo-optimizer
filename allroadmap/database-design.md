# Database Design - MomoOpti

Ce document décrit le schéma de la base de données relationnelle PostgreSQL utilisée par MomoOpti, avec la nouvelle architecture centrée sur l'utilisateur et ses moyens de paiement préférés.

---

## 📊 Diagramme conceptuel simplifié
┌─────────────────┐ ┌─────────────────────┐ ┌─────────────────────┐
│ users │ │ user_methods │ │ methods │
│─────────────────│ │─────────────────────│ │─────────────────────│
│ id (PK) │──────│ user_id (FK) │──────│ id (PK) │
│ name │ │ method_id (FK) │ │ name │
│ email │ │ type (receipt/send) │ │ code │
│ password │ │ account_id │ │ category (mobile/ │
│ country_code │ │ label │ │ bank/other) │
│ created_at │ │ is_default │ │ country_code │
│ updated_at │ │ created_at │ │ logo_url │
└─────────────────┘ │ updated_at │ │ is_active │
└─────────────────────┘ └─────────────────────┘
│ │
│ │
│ │
┌─────────────────┐ ┌─────────▼─────────────┐ ┌──────▼──────────────┐
│ subscriptions │ │ receipt_fees │ │ sending_fees │
│─────────────────│ │────────────────────────│ │─────────────────────│
│ id (PK) │ │ id (PK) │ │ id (PK) │
│ user_id (FK) │ │ method_id (FK) │ │ method_id (FK) │
│ plan │ │ country_code │ │ country_code │
│ status │ │ min_amount │ │ min_amount │
│ start_date │ │ max_amount │ │ max_amount │
│ end_date │ │ fee_amount │ │ fee_amount │
│ transaction_id │ │ fee_type │ │ fee_type │
│ created_at │ │ created_at │ │ created_at │
│ updated_at │ │ updated_at │ │ updated_at │
└─────────────────┘ └────────────────────────┘ └─────────────────────┘
│ │
│ │
│ │
┌─────────────────┐ ┌─────────▼───────────────────────────▼──────────────────┐
│ optimization_ │ │ optimization_history │
│ history │ │────────────────────────────────────────────────────────│
│─────────────────│ │ id (PK) │
│ id (PK) │ │ user_id (FK) │
│ user_id (FK) │ │ type (receipt/send) │
│ type │ │ amount │
│ amount │ │ selected_method_id (FK) │
│ selected_method │ │ total_fee │
│ total_fee │ │ savings │
│ savings │ │ alternatives (JSON) │
│ created_at │ │ created_at │
└─────────────────┘ └───────────────────────────────────────────────────────┘

text

---

## 📋 Détail des Tables

### 1. `users`

**Description** : Stocke les informations des utilisateurs de l'application.

| Champ | Type | Contrainte | Description |
|-------|------|------------|-------------|
| `id` | UUID | PRIMARY KEY | Identifiant unique (UUID v4) |
| `name` | VARCHAR(100) | NOT NULL | Nom complet de l'utilisateur |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | Adresse email |
| `password` | VARCHAR(255) | NOT NULL | Mot de passe haché |
| `country_code` | VARCHAR(10) | NOT NULL | Code pays de l'utilisateur (BJ, CI, etc.) |
  `is_admin`	|BOOLEAN|	DEFAULT false|	Indique si l'utilisateur est un administrateur (accès au backoffice)
| `preferred_receipt_methods` | JSON | NULL | Liste des IDs des méthodes préférées pour la réception |
| `preferred_sending_methods` | JSON | NULL | Liste des IDs des méthodes préférées pour l'envoi |
| `subscription` | ENUM('free', 'premium', 'pro', 'business') | DEFAULT 'free' | Plan d'abonnement |
| `subscription_expires_at` | TIMESTAMP | NULL | Date d'expiration de l'abonnement (NULL pour gratuit) |
| `remember_token` | VARCHAR(100) | NULL | Token de session |
| `email_verified_at` | TIMESTAMP | NULL | Date de vérification email |
| `preferences` | JSON | NULL | Préférences utilisateur (langue, notifications, etc.) |
| `created_at` | TIMESTAMP | NOT NULL | Date de création |
| `updated_at` | TIMESTAMP | NOT NULL | Date de dernière mise à jour |

**Index** : `email` (unique), `country_code`, `subscription`

**Note** : Les colonnes JSON `preferred_receipt_methods` et `preferred_sending_methods` stockent une liste d'IDs des méthodes sélectionnées par l'utilisateur pour filtrer les options affichées dans les classements. Pour plus de flexibilité, on peut utiliser la table `user_methods` (voir ci-dessous).

---

### 2. `methods`

**Description** : Liste de tous les moyens de paiement disponibles dans l'application (à l'échelle mondiale).

| Champ | Type | Contrainte | Description |
|-------|------|------------|-------------|
| `id` | UUID | PRIMARY KEY | Identifiant unique |
| `name` | VARCHAR(100) | NOT NULL | Nom du moyen (MTN MoMo, Moov Money, etc.) |
| `code` | VARCHAR(50) | UNIQUE, NOT NULL | Code unique (mtn, moov, celtiis, orange_ci, wise, etc.) |
| `category` | ENUM('mobile_money', 'bank', 'international', 'crypto', 'other') | NOT NULL | Catégorie du moyen |
| `country_code` | VARCHAR(10) | NULL | Pays de disponibilité (NULL si international) |
| `logo_url` | VARCHAR(255) | NULL | URL du logo (CDN) |
| `color_primary` | VARCHAR(7) | NULL | Couleur principale (ex: #F5A623) |
| `currency` | VARCHAR(3) | DEFAULT 'XOF' | Devise par défaut (XOF, EUR, USD, etc.) |
| `is_active` | BOOLEAN | DEFAULT true | Si le moyen est actif dans l'app |
| `created_at` | TIMESTAMP | NOT NULL | Date de création |
| `updated_at` | TIMESTAMP | NOT NULL | Date de mise à jour |

**Seed initial** :
- MTN MoMo (BJ, XOF)
- Moov Money (BJ, XOF)
- Celtiis Cash (BJ, XOF)
- Orange Money (CI, XOF)
- Orange Money (SN, XOF)
- Wise (International, EUR/USD)
- PayPal (International, EUR/USD)
- Ecobank (BJ, XOF)
- Banque Atlantique (BJ, XOF)

**Index** : `code` (unique), `country_code`, `category`

---

### 3. `user_methods`

**Description** : Table de liaison entre les utilisateurs et les méthodes qu'ils ont enregistrées (pour la réception et/ou l'envoi).

| Champ | Type | Contrainte | Description |
|-------|------|------------|-------------|
| `id` | UUID | PRIMARY KEY | Identifiant unique |
| `user_id` | UUID | FOREIGN KEY (users.id) | Référence à l'utilisateur |
| `method_id` | UUID | FOREIGN KEY (methods.id) | Référence à la méthode |
| `type` | ENUM('receipt', 'send', 'both') | NOT NULL | Type d'utilisation (réception, envoi, ou les deux) |
| `account_id` | VARCHAR(255) | NULL | Identifiant du compte (numéro MTN, IBAN, etc.) |
| `account_label` | VARCHAR(100) | NULL | Libellé personnalisé (ex: "Compte perso", "Pro") |
| `is_default` | BOOLEAN | DEFAULT false | Si c'est le moyen par défaut pour ce type |
| `created_at` | TIMESTAMP | NOT NULL | Date de création |
| `updated_at` | TIMESTAMP | NOT NULL | Date de mise à jour |

**Index** : `user_id`, `method_id`, `type`

**Contrainte** : Un utilisateur ne peut pas avoir deux fois la même méthode pour le même type (unique `user_id + method_id + type`).

**Note** : La table `user_methods` offre plus de flexibilité que les champs JSON dans `users`. Elle permet de stocker des informations supplémentaires (compte, label, etc.) et de gérer facilement les modifications.

---

### 4. `receipt_fees`

**Description** : Grille tarifaire des frais de retrait/réception par méthode et par palier (pour chaque pays).

| Champ | Type | Contrainte | Description |
|-------|------|------------|-------------|
| `id` | UUID | PRIMARY KEY | Identifiant unique |
| `method_id` | UUID | FOREIGN KEY (methods.id) | Référence à la méthode |
| `country_code` | VARCHAR(10) | NOT NULL | Pays de validité de la grille |
| `min_amount` | DECIMAL(15,2) | NOT NULL | Montant minimum du palier |
| `max_amount` | DECIMAL(15,2) | NOT NULL | Montant maximum du palier |
| `fee_amount` | DECIMAL(15,2) | NOT NULL | Montant des frais (fixe ou pourcentage) |
| `fee_type` | ENUM('fixed', 'percentage') | NOT NULL | Type de frais (fixe ou % du montant) |
| `created_at` | TIMESTAMP | NOT NULL | Date de création |
| `updated_at` | TIMESTAMP | NOT NULL | Date de mise à jour |

**Index** : `method_id`, `country_code`, `min_amount`, `max_amount`

**Contrainte** : Les paliers ne doivent pas se chevaucher pour une même méthode et un même pays.

---

### 5. `sending_fees`

**Description** : Grille tarifaire des frais d'envoi par méthode et par palier (pour chaque pays).

| Champ | Type | Contrainte | Description |
|-------|------|------------|-------------|
| `id` | UUID | PRIMARY KEY | Identifiant unique |
| `method_id` | UUID | FOREIGN KEY (methods.id) | Référence à la méthode |
| `country_code` | VARCHAR(10) | NOT NULL | Pays de validité de la grille |
| `min_amount` | DECIMAL(15,2) | NOT NULL | Montant minimum du palier |
| `max_amount` | DECIMAL(15,2) | NOT NULL | Montant maximum du palier |
| `fee_amount` | DECIMAL(15,2) | NOT NULL | Montant des frais (fixe ou pourcentage) |
| `fee_type` | ENUM('fixed', 'percentage') | NOT NULL | Type de frais (fixe ou % du montant) |
| `created_at` | TIMESTAMP | NOT NULL | Date de création |
| `updated_at` | TIMESTAMP | NOT NULL | Date de mise à jour |

**Index** : `method_id`, `country_code`, `min_amount`, `max_amount`

**Contrainte** : Les paliers ne doivent pas se chevaucher pour une même méthode et un même pays.

---

### 6. `subscriptions`

**Description** : Historique et gestion des abonnements payants.

| Champ | Type | Contrainte | Description |
|-------|------|------------|-------------|
| `id` | UUID | PRIMARY KEY | Identifiant unique |
| `user_id` | UUID | FOREIGN KEY (users.id) | Référence à l'utilisateur |
| `plan` | ENUM('premium', 'pro', 'business') | NOT NULL | Plan souscrit |
| `status` | ENUM('active', 'expired', 'cancelled') | DEFAULT 'active' | Statut de l'abonnement |
| `start_date` | TIMESTAMP | NOT NULL | Date de début |
| `end_date` | TIMESTAMP | NOT NULL | Date de fin |
| `payment_method` | VARCHAR(50) | NULL | Moyen de paiement utilisé (ex: 'fedapay') |
| `transaction_id` | VARCHAR(255) | NULL | ID de transaction (FedaPay) |
| `amount_paid` | DECIMAL(15,2) | NULL | Montant payé |
| `currency` | VARCHAR(3) | DEFAULT 'XOF' | Devise du paiement |
| `created_at` | TIMESTAMP | NOT NULL | Date de création |
| `updated_at` | TIMESTAMP | NOT NULL | Date de mise à jour |

**Index** : `user_id`, `status`, `end_date`

---

### 7. `optimization_history`

**Description** : Historique des optimisations effectuées par les utilisateurs (pour les modes "Je reçois" et "J'envoie").

| Champ | Type | Contrainte | Description |
|-------|------|------------|-------------|
| `id` | UUID | PRIMARY KEY | Identifiant unique |
| `user_id` | UUID | FOREIGN KEY (users.id) | Référence à l'utilisateur |
| `type` | ENUM('receipt', 'send') | NOT NULL | Type d'opération (réception ou envoi) |
| `amount` | DECIMAL(15,2) | NOT NULL | Montant de la transaction |
| `selected_method_id` | UUID | FOREIGN KEY (methods.id) | Méthode choisie par l'utilisateur |
| `total_fee` | DECIMAL(15,2) | NOT NULL | Frais totaux de l'option choisie |
| `savings` | DECIMAL(15,2) | NOT NULL | Économies réalisées par rapport à la moins bonne option |
| `alternatives` | JSON | NULL | Liste des autres options avec leurs frais (pour référence) |
| `created_at` | TIMESTAMP | NOT NULL | Date de l'optimisation |

**Index** : `user_id`, `created_at` (desc)

**Note** : `alternatives` peut stocker un tableau comme :
```json
[
  {"method": "mtn", "fee": 2000},
  {"method": "moov", "fee": 1750},
  {"method": "celtiis", "fee": 2000}
]
8. countries (extension future)
Description : Liste des pays supportés par l'application (pour l'extension internationale).

Champ	Type	Contrainte	Description
id	UUID	PRIMARY KEY	Identifiant unique
name	VARCHAR(100)	NOT NULL	Nom du pays
code	VARCHAR(3)	UNIQUE, NOT NULL	Code ISO (BJ, CI, SN, etc.)
currency	VARCHAR(3)	NOT NULL	Devise (XOF, EUR, USD, etc.)
flag_url	VARCHAR(255)	NULL	URL du drapeau
is_active	BOOLEAN	DEFAULT true	Si le pays est actif dans l'app
created_at	TIMESTAMP	NOT NULL	Date de création
updated_at	TIMESTAMP	NOT NULL	Date de mise à jour
Seed initial : Bénin (BJ), Côte d'Ivoire (CI), Sénégal (SN), Togo (TG), Ghana (GH), etc.

🔗 Relations entre les tables
text
users (1) ───┬─── (n) user_methods (type: receipt/send)
              ├─── (n) subscriptions
              ├─── (n) optimization_history
              └─── (n) preferences (JSON, optionnel)

methods (1) ─┬─── (n) user_methods
              ├─── (n) receipt_fees
              ├─── (n) sending_fees
              ├─── (n) optimization_history (via selected_method_id)
              └─── (n) methods (via parent_id pour la hiérarchie, optionnel)

receipt_fees (n) ─── (1) methods
sending_fees (n) ─── (1) methods
countries (1) ─── (n) methods (via country_code)
🧪 Exemple de données
methods
id	name	code	category	country_code	color_primary
1	MTN MoMo (Bénin)	mtn_bj	mobile_money	BJ	#F5A623
2	Moov Money (Bénin)	moov_bj	mobile_money	BJ	#0056A4
3	Celtiis Cash (Bénin)	celtiis_bj	mobile_money	BJ	#00A651
4	Orange Money (Côte d'Ivoire)	orange_ci	mobile_money	CI	#FF6600
5	Wise	wise_international	international	NULL	#00B4AB
6	PayPal	paypal_international	international	NULL	#003087
receipt_fees (MTN MoMo Bénin)
method_id	country_code	min_amount	max_amount	fee_amount	fee_type
1	BJ	1	500	50	fixed
1	BJ	501	5000	125	fixed
1	BJ	5001	10000	225	fixed
1	BJ	10001	20000	375	fixed
1	BJ	20001	50000	700	fixed
1	BJ	50001	100000	1000	fixed
1	BJ	100001	200000	2000	fixed
user_methods (exemple de Jean)
user_id	method_id	type	account_id	account_label	is_default
1	1	both	90000001	MTN Perso	true
1	3	receipt	90000002	Celtiis Pro	false
1	5	send	jean@wise.com	Wise Pro	false
🔧 Migrations Laravel (exemples)
Migration pour methods
php
Schema::create('methods', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('name', 100);
    $table->string('code', 50)->unique();
    $table->enum('category', ['mobile_money', 'bank', 'international', 'crypto', 'other']);
    $table->string('country_code', 10)->nullable();
    $table->string('logo_url', 255)->nullable();
    $table->string('color_primary', 7)->nullable();
    $table->string('currency', 3)->default('XOF');
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index(['country_code', 'category']);
});
Migration pour user_methods
php
Schema::create('user_methods', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignUuid('method_id')->constrained('methods')->onDelete('cascade');
    $table->enum('type', ['receipt', 'send', 'both']);
    $table->string('account_id', 255)->nullable();
    $table->string('account_label', 100)->nullable();
    $table->boolean('is_default')->default(false);
    $table->timestamps();

    $table->unique(['user_id', 'method_id', 'type']);
    $table->index(['user_id', 'type']);
});
Migration pour receipt_fees
php
Schema::create('receipt_fees', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('method_id')->constrained('methods')->onDelete('cascade');
    $table->string('country_code', 10);
    $table->decimal('min_amount', 15, 2);
    $table->decimal('max_amount', 15, 2);
    $table->decimal('fee_amount', 15, 2);
    $table->enum('fee_type', ['fixed', 'percentage'])->default('fixed');
    $table->timestamps();

    $table->unique(['method_id', 'country_code', 'min_amount', 'max_amount']);
    $table->index(['method_id', 'country_code']);
});
📌 Spécificités pour l'extension internationale
Point	Approche
Gestion des devises	Utiliser currency dans methods et countries ; conversion via un service tiers (ex: API de taux de change)
Gestion des pays	Utiliser la table countries ; filtrer les méthodes par country_code
Gestion des frais inter-pays	Ajouter une table cross_border_fees si nécessaire (pour les transferts entre pays)
Gestion des langues	Ajouter locale dans les préférences utilisateur ; utiliser les fichiers de traduction Laravel
Dernière mise à jour : 13 août 2026