# Database Summary - MomoOpti

Ce document récapitule l'ensemble des tables de la base de données, leurs colonnes principales, et les relations qu'elles entretiennent entre elles. Il est conçu comme une référence rapide pour le développement.

---

## 1. Table `users` — Utilisateurs

**Rôle** : Stocke les informations des utilisateurs de l'application.

**Contenu** : Chaque utilisateur a un identifiant unique (UUID), un nom complet, une adresse email unique utilisée pour la connexion, un mot de passe haché, et un code pays (ex: BJ pour Bénin, CI pour Côte d'Ivoire). L'utilisateur a un plan d'abonnement (free, premium, pro, business) avec une date d'expiration. 
+ Un champ `is_admin` (booléen, par défaut false) permet de distinguer les administrateurs des utilisateurs standards.
Il peut également avoir des préférences stockées au format JSON (langue, notifications, etc.). Les dates de création et de dernière mise à jour sont également enregistrées.

**Relations** :
- Un utilisateur peut avoir plusieurs moyens de paiement enregistrés via la table `user_methods` (relation one-to-many).
- Un utilisateur peut avoir plusieurs abonnements dans la table `subscriptions` (relation one-to-many).
- Un utilisateur peut avoir plusieurs historiques d'optimisation dans la table `optimization_history` (relation one-to-many).

---

## 2. Table `methods` — Moyens de paiement

**Rôle** : Liste de tous les moyens de paiement disponibles dans l'application, qu'ils soient locaux ou internationaux.

**Contenu** : Chaque moyen a un identifiant unique, un nom (ex: "MTN MoMo", "Wise"), un code unique (ex: "mtn_bj", "wise"), une catégorie (mobile_money, bank, international, crypto, other), un pays de disponibilité (NULL si international), une URL de logo, une couleur primaire pour l'affichage, une devise par défaut, et un statut actif ou inactif.

**Relations** :
- Un moyen peut être utilisé par plusieurs utilisateurs via la table `user_methods` (relation one-to-many).
- Un moyen peut avoir plusieurs paliers de frais de réception dans la table `receipt_fees` (relation one-to-many).
- Un moyen peut avoir plusieurs paliers de frais d'envoi dans la table `sending_fees` (relation one-to-many).
- Un moyen peut être choisi dans plusieurs historiques d'optimisation via la table `optimization_history` (relation one-to-many).

---

## 3. Table `user_methods` — Moyens préférés des utilisateurs

**Rôle** : Table de liaison qui permet à chaque utilisateur d'enregistrer ses moyens de paiement préférés et de les configurer selon ses besoins.

**Contenu** : Chaque enregistrement associe un utilisateur à un moyen de paiement, et précise le type d'utilisation (receipt pour la réception, send pour l'envoi, ou both pour les deux). L'utilisateur peut également stocker son identifiant de compte (numéro MTN, IBAN, etc.) et un libellé personnalisé (ex: "Compte perso", "Pro"). Un champ `is_default` permet de définir le moyen par défaut pour chaque type. La contrainte d'unicité garantit qu'un utilisateur ne peut pas avoir deux fois le même moyen pour le même type.

**Relations** :
- Cette table appartient à un utilisateur (relation many-to-one vers `users`).
- Cette table référence un moyen de paiement (relation many-to-one vers `methods`).

---

## 4. Table `receipt_fees` — Frais de réception/retrait

**Rôle** : Stocke les grilles tarifaires des frais de réception (retrait) pour chaque moyen de paiement, par pays et par palier de montant.

**Contenu** : Chaque enregistrement est lié à un moyen de paiement et à un pays. Il définit un palier de montant (montant minimum et maximum) ainsi que le montant des frais correspondant, et le type de frais (fixe ou pourcentage). La contrainte d'unicité garantit qu'un palier ne peut pas être dupliqué pour le même moyen et le même pays.

**Relations** :
- Cette table appartient à un moyen de paiement (relation many-to-one vers `methods`).
- Cette table est liée à un pays via le champ `country_code` (relation many-to-one vers `countries`).

---

## 5. Table `sending_fees` — Frais d'envoi

**Rôle** : Stocke les grilles tarifaires des frais d'envoi pour chaque moyen de paiement, par pays et par palier de montant.

**Contenu** : Structure identique à la table `receipt_fees`. Chaque enregistrement est lié à un moyen de paiement et à un pays. Il définit un palier de montant (montant minimum et maximum) ainsi que le montant des frais correspondant, et le type de frais (fixe ou pourcentage). La contrainte d'unicité garantit qu'un palier ne peut pas être dupliqué pour le même moyen et le même pays.

**Relations** :
- Cette table appartient à un moyen de paiement (relation many-to-one vers `methods`).
- Cette table est liée à un pays via le champ `country_code` (relation many-to-one vers `countries`).

---

## 6. Table `subscriptions` — Abonnements

**Rôle** : Gère l'historique des abonnements payants des utilisateurs.

**Contenu** : Chaque enregistrement est lié à un utilisateur et définit le plan souscrit (premium, pro, business), le statut (active, expired, cancelled), les dates de début et de fin. Il stocke également le moyen de paiement utilisé (ex: "fedapay"), l'identifiant de transaction, le montant payé et la devise. Cela permet de suivre l'historique complet des abonnements de chaque utilisateur.

**Relations** :
- Cette table appartient à un utilisateur (relation many-to-one vers `users`).

---

## 7. Table `optimization_history` — Historique des optimisations

**Rôle** : Enregistre chaque analyse effectuée par un utilisateur (mode "Je reçois" ou "J'envoie") avec le résultat de l'optimisation.

**Contenu** : Chaque enregistrement est lié à un utilisateur et précise le type d'opération (receipt ou send), le montant analysé, la méthode choisie, les frais totaux de l'option choisie, et les économies réalisées par rapport à la moins bonne option. Un champ JSON `alternatives` stocke la liste des autres options avec leurs frais pour référence future.

**Relations** :
- Cette table appartient à un utilisateur (relation many-to-one vers `users`).
- Cette table référence la méthode choisie (relation many-to-one vers `methods`).

---

## 8. Table `countries` — Pays supportés

**Rôle** : Liste des pays supportés par l'application, utilisée pour l'extension internationale et le filtrage des moyens de paiement.

**Contenu** : Chaque pays a un identifiant unique, un nom, un code ISO (ex: BJ, CI, SN), une devise (ex: XOF, EUR), une URL de drapeau, et un statut actif ou inactif.

**Relations** :
- Un pays peut avoir plusieurs moyens de paiement via `methods` (relation one-to-many).
- Un pays peut avoir plusieurs utilisateurs via `users` (relation one-to-many).
- Un pays peut avoir plusieurs grilles de frais de réception via `receipt_fees` (relation one-to-many).
- Un pays peut avoir plusieurs grilles de frais d'envoi via `sending_fees` (relation one-to-many).

---

## Relations récapitulatives

**Les relations one-to-many (1 → n) :**

- **users** → user_methods : Un utilisateur peut enregistrer plusieurs moyens de paiement.
- **users** → subscriptions : Un utilisateur peut avoir plusieurs abonnements (historique).
- **users** → optimization_history : Un utilisateur peut effectuer plusieurs optimisations.
- **methods** → user_methods : Un moyen peut être utilisé par plusieurs utilisateurs.
- **methods** → receipt_fees : Un moyen peut avoir plusieurs paliers de frais de réception.
- **methods** → sending_fees : Un moyen peut avoir plusieurs paliers de frais d'envoi.
- **methods** → optimization_history : Un moyen peut être choisi dans plusieurs optimisations.
- **countries** → methods : Un pays peut avoir plusieurs moyens de paiement.
- **countries** → users : Un pays peut avoir plusieurs utilisateurs.
- **countries** → receipt_fees : Un pays peut avoir plusieurs grilles de frais de réception.
- **countries** → sending_fees : Un pays peut avoir plusieurs grilles de frais d'envoi.

**Les relations many-to-one (n → 1) :**

- **user_methods** → users : Un enregistrement appartient à un utilisateur.
- **user_methods** → methods : Un enregistrement référence un moyen de paiement.
- **receipt_fees** → methods : Un enregistrement appartient à un moyen de paiement.
- **sending_fees** → methods : Un enregistrement appartient à un moyen de paiement.
- **subscriptions** → users : Un enregistrement appartient à un utilisateur.
- **optimization_history** → users : Un enregistrement appartient à un utilisateur.
- **optimization_history** → methods : Un enregistrement référence un moyen de paiement.

---

## Notes complémentaires

1. **Clés primaires** : Toutes les tables utilisent des identifiants de type UUID pour une meilleure scalabilité et pour éviter les collisions.

2. **Clés étrangères** : Toutes les relations sont configurées avec `ON DELETE CASCADE` pour maintenir l'intégrité référentielle et supprimer automatiquement les enregistrements dépendants.

3. **Champs JSON** : Les champs `preferences` dans `users` et `alternatives` dans `optimization_history` sont de type JSON pour stocker des données structurées sans multiplier les tables.

4. **Devises** : Le champ `currency` est présent dans plusieurs tables pour gérer les différentes devises et faciliter l'extension internationale.

5. **Extension internationale** : La table `countries` et le champ `country_code` dans plusieurs tables sont conçus pour permettre une extension facile à d'autres pays et régions.

---

*Dernière mise à jour : 13 août 2026*