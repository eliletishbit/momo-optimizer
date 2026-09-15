# Features List - MomoOpti

Ce document liste l'ensemble des fonctionnalités de l'application MomoOpti, organisées par modules et par version.

---

## 🧩 Module 0 : Authentification et Profil Utilisateur

### 0.1 Inscription et Connexion

**Description** : L'utilisateur crée son compte et s'authentifie pour accéder à ses données personnalisées.

**Spécifications** :
- Formulaire d'inscription (nom, email, mot de passe)
- Formulaire de connexion
- Vérification de l'email (lien de confirmation)
- Réinitialisation du mot de passe
- Connexion avec Google (optionnel via Laravel Socialite)

**Critères d'acceptation** :
- [ ] L'utilisateur peut créer un compte en moins de 2 minutes
- [ ] L'email de confirmation est envoyé
- [ ] La réinitialisation de mot de passe fonctionne
- [ ] L'utilisateur connecté a accès à son espace personnel

---

### 0.2 Configuration des moyens de paiement préférés

**Description** : L'utilisateur enregistre les moyens de paiement qu'il utilise pour **envoyer** et **recevoir** de l'argent.

**Spécifications** :
- **Moyens de réception** (pour le mode "Je reçois") :
  - MTN Mobile Money (Bénin)
  - Moov Money (Bénin)
  - Celtiis Cash (Bénin)
  - Orange Money (Côte d'Ivoire, Sénégal, etc.)
  - Comptes bancaires (Banque Atlantique, Ecobank, etc.)
  - Comptes internationaux (Wise, PayPal, etc.)
  - Autres (Mobile Money d'autres pays, etc.)
- **Moyens d'envoi** (pour le mode "J'envoie") :
  - Même liste que ci-dessus, mais l'utilisateur choisit ceux qu'il utilise pour envoyer
- Pour chaque moyen, l'utilisateur peut préciser :
  - Son numéro de compte ou identifiant
  - Son pays (pour les moyens internationaux)
  - Une étiquette personnalisée (ex: "MTN personnel", "Compte Ecobank pro")
- L'utilisateur peut **ajouter** ou **supprimer** un moyen à tout moment
- L'utilisateur peut **définir un moyen par défaut** pour chaque mode (optionnel)

**Critères d'acceptation** :
- [ ] L'utilisateur peut ajouter au moins 3 moyens pour chaque mode
- [ ] Les moyens sont persistants et affichés dans les classements
- [ ] L'utilisateur peut modifier ou supprimer un moyen
- [ ] Les données sont stockées dans la base de données (champs JSON ou table `user_methods`)

---

## 🧩 Module 1 : Mode "Je reçois de l'argent"

### 1.1 Saisie du montant

**Description** : L'utilisateur entre le montant qu'il doit recevoir.

**Spécifications** :
- Champ de saisie numérique
- Affichage du montant en FCFA (ou en devise locale)
- Suggestions de montants rapides (10 000, 25 000, 50 000, 100 000, 500 000)
- Validation : montant > 0

**Critères d'acceptation** :
- [ ] Le champ est présent et fonctionnel
- [ ] Le montant est correctement formaté (espaces entre milliers)
- [ ] Les suggestions rapides sont cliquables
- [ ] Une erreur est affichée si le montant est invalide

---

### 1.2 Analyse et classement des options de réception

**Description** : L'application calcule les frais de retrait pour chaque moyen de paiement enregistré par l'utilisateur, et affiche un classement clair.

**Spécifications** :
- **Classement** : Les options sont triées par frais croissants (du moins cher au plus cher)
- **Affichage** :
  - 🥇 1. [Nom du moyen] : [frais] FCFA (le moins cher)
  - 🥈 2. [Nom du moyen] : [frais] FCFA (+[différence] FCFA)
  - 🥉 3. [Nom du moyen] : [frais] FCFA (+[différence] FCFA)
- **Détails supplémentaires** :
  - Frais totaux
  - Montant net reçu (montant - frais)
  - Taux de perte (frais / montant)
- **Option combinée multi-réseaux** (secondaire) :
  - L'application propose éventuellement une combinaison de retraits sur plusieurs réseaux pour minimiser les frais (si l'utilisateur a plusieurs moyens enregistrés)
  - Cette option est affichée en bas du classement avec la mention "Option combinée (juste pour info)"
  - Elle n'est pas proposée comme option principale, mais comme information complémentaire

**Critères d'acceptation** :
- [ ] Le classement est trié par frais croissants
- [ ] Les frais sont calculés correctement selon les paliers de chaque réseau
- [ ] Les résultats s'affichent en moins de 1 seconde
- [ ] L'option combinée est affichée si elle est pertinente (et si l'utilisateur a plus d'un moyen)

---

### 1.3 Communication du moyen de réception

**Description** : Après avoir choisi la meilleure option, l'utilisateur peut facilement communiquer le moyen de réception à l'envoyeur.

**Spécifications** :
- Un bouton **"Partager le moyen"** ou **"Générer un message"**
- Le message généré contient :
  - Le montant à envoyer
  - Le moyen de réception (nom du réseau, numéro)
  - Une note personnalisable
- Le message peut être copié ou partagé via WhatsApp, SMS, email, etc.

**Critères d'acceptation** :
- [ ] Le bouton est visible après le choix de l'option
- [ ] Le message est généré automatiquement
- [ ] L'utilisateur peut modifier le message avant de le partager
- [ ] Le partage fonctionne sur mobile (via l'API Web Share)

---

## 🧩 Module 2 : Mode "J'envoie de l'argent"

### 2.1 Saisie du montant

**Description** : L'utilisateur entre le montant qu'il veut envoyer (identique à 1.1).

**Critères d'acceptation** : Identiques à 1.1.

---

### 2.2 Analyse et classement des options d'envoi

**Description** : L'application calcule les frais d'envoi pour chaque moyen de paiement enregistré par l'utilisateur pour l'envoi, et affiche un classement clair.

**Spécifications** :
- **Classement** : Les options sont triées par frais croissants (du moins cher au plus cher)
- **Affichage** :
  - 🥇 1. [Nom du moyen] : [frais] FCFA (le moins cher)
  - 🥈 2. [Nom du moyen] : [frais] FCFA (+[différence] FCFA)
  - 🥉 3. [Nom du moyen] : [frais] FCFA (+[différence] FCFA)
- **Détails supplémentaires** :
  - Frais totaux
  - Montant total à débiter (montant + frais)
  - Taux de perte (frais / montant)
- **Option combinée multi-réseaux** (secondaire) : non applicable pour l'envoi (un seul envoi par transaction)

**Critères d'acceptation** :
- [ ] Le classement est trié par frais croissants
- [ ] Les frais sont calculés correctement selon les paliers de chaque réseau
- [ ] Les résultats s'affichent en moins de 1 seconde
- [ ] Le montant total à débiter est clairement affiché

---

### 2.3 Action après le choix

**Description** : Après avoir choisi la meilleure option, l'utilisateur est guidé pour effectuer l'envoi.

**Spécifications** :
- L'application affiche un récapitulatif :
  - Montant à envoyer
  - Moyen d'envoi choisi
  - Frais
  - Montant total à débiter
- Un bouton **"Aller sur l'application d'envoi"** (si disponible) ou **"Copier les informations"**
- L'utilisateur est invité à effectuer l'envoi via son application de l'opérateur (MTN MoMo, etc.)

**Critères d'acceptation** :
- [ ] Le récapitulatif est clair et complet
- [ ] Les informations peuvent être copiées facilement
- [ ] Un message de confirmation est affiché après l'envoi (facultatif, via saisie manuelle)

---

## 🧩 Module 3 : Historique et Statistiques

### 3.1 Historique des transactions

**Description** : L'utilisateur peut consulter l'historique de ses analyses (réceptions et envois).

**Spécifications** :
- Liste des transactions passées avec :
  - Date et heure
  - Type (Réception / Envoi)
  - Montant
  - Moyen choisi
  - Frais payés (estimés)
  - Économies réalisées (par rapport à la moins bonne option)
- Possibilité de filtrer par type, par période, par moyen
- Possibilité de supprimer une transaction de l'historique

**Critères d'acceptation** :
- [ ] La liste s'affiche correctement
- [ ] Les filtres fonctionnent
- [ ] Les économies sont calculées correctement

---

### 3.2 Statistiques d'économies

**Description** : L'utilisateur voit un récapitulatif des économies réalisées grâce à l'application.

**Spécifications** :
- Total des frais économisés (cumulé)
- Moyenne des économies par transaction
- Évolution mensuelle des économies
- Comparaison avec la moyenne des utilisateurs (optionnel)

**Critères d'acceptation** :
- [ ] Les statistiques s'affichent clairement
- [ ] Les chiffres sont à jour
- [ ] Un graphique simple (barres ou courbe) est affiché

---

## 🧩 Module 4 : Alertes et Notifications

### 4.1 Alertes de baisse de frais

**Description** : L'utilisateur est notifié lorsqu'un réseau baisse ses frais.

**Spécifications** :
- L'utilisateur peut définir des alertes par moyen (ex: "Préviens-moi si MTN baisse ses frais de retrait")
- L'application vérifie périodiquement les grilles tarifaires (via un job planifié)
- Envoi d'une notification par email ou in-app

**Critères d'acceptation** :
- [ ] L'utilisateur peut créer une alerte
- [ ] L'alerte est vérifiée quotidiennement
- [ ] La notification est envoyée lorsque la condition est remplie

---

### 4.2 Notifications in-app

**Description** : Les utilisateurs reçoivent des notifications dans l'application.

**Spécifications** :
- Notification lors de la publication d'une nouvelle grille tarifaire
- Notification de rappel (ex: "Vous n'avez pas utilisé l'application depuis 7 jours")
- Notification de confirmation après une analyse (optionnel)

**Critères d'acceptation** :
- [ ] Les notifications s'affichent dans l'application
- [ ] Les notifications peuvent être activées/désactivées dans les préférences
- [ ] Les notifications sont stockées en base de données

---

## 🧩 Module 5 : PWA (Progressive Web App)

### 5.1 Installation sur mobile

**Description** : L'application est installable sur l'écran d'accueil des smartphones.

**Spécifications** :
- Fichier `manifest.json` avec les icônes et les couleurs
- Service Worker pour le cache (fonctionnalité hors ligne partielle)
- Bannière d'installation proposée aux utilisateurs

**Critères d'acceptation** :
- [ ] L'application s'installe sur Android (Chrome) et iOS (Safari)
- [ ] Les icônes s'affichent correctement
- [ ] L'application s'ouvre en mode plein écran
- [ ] Les pages sont accessibles hors ligne (au moins la page d'accueil)

---

## 🧩 Module 6 : Administration (Backoffice)

### 6.1 Gestion des grilles tarifaires

**Description** : L'administrateur peut mettre à jour les grilles tarifaires.

**Spécifications** :
- Interface pour ajouter, modifier ou supprimer des paliers de frais
- Historique des modifications
- Possibilité de désactiver temporairement un réseau

**Critères d'acceptation** :
- [ ] L'administrateur peut mettre à jour les paliers
- [ ] Les modifications sont appliquées immédiatement
- [ ] Un historique est conservé pour traçabilité

---

### 6.2 Gestion des utilisateurs

**Description** : L'administrateur peut gérer les utilisateurs et leurs abonnements.

**Spécifications** :
- Liste des utilisateurs avec filtres (par plan, date, etc.)
- Modification du plan d'un utilisateur
- Extension manuelle d'un abonnement
- Désactivation d'un compte

**Critères d'acceptation** :
- [ ] L'administrateur peut voir tous les utilisateurs
- [ ] Il peut modifier les plans
- [ ] Il peut étendre ou annuler un abonnement

---

### 6.3 Statistiques globales

**Description** : Tableau de bord avec les indicateurs clés.

**Spécifications** :
- Nombre d'utilisateurs (gratuits, payants)
- Revenus mensuels
- Taux de conversion
- Nombre d'analyses par jour
- Économies totales réalisées par les utilisateurs

**Critères d'acceptation** :
- [ ] Les chiffres sont affichés clairement
- [ ] Les graphiques sont visuels
- [ ] Les données sont actualisées en temps réel (ou quasi)

---

## 📈 Fonctionnalités futures (Post-MVP)

| Fonctionnalité | Version cible | Description |
|----------------|---------------|-------------|
| **Extension internationale** | V2.0 | Ajout de pays, devises, réseaux étrangers |
| **Transfert direct via l'application** | V1.2 | API opérateur pour effectuer l'envoi directement |
| **Intégration avec les caisses** | V2.0 | Plugins pour les systèmes de caisse (ex: Chrome, Android) |
| **API publique** | V2.5 | Permet aux commerçants d'intégrer l'optimisation dans leur propre système |
| **Gamification** | V2.5 | Badges, classements, défis pour encourager l'utilisation |
| **Analyse prédictive** | V3.0 | Prédiction des futures baisses de frais via IA |
| **Portefeuille virtuel** | V3.0 | Stockage d'argent à optimiser en temps réel |

---

## ✅ Critères de validation du MVP

Pour que le MVP soit considéré comme terminé, les fonctionnalités suivantes doivent être opérationnelles :

- [ ] **Module 0.1** : Inscription et connexion
- [ ] **Module 0.2** : Configuration des moyens de paiement préférés
- [ ] **Module 1.1** : Saisie du montant (mode "Je reçois")
- [ ] **Module 1.2** : Analyse et classement des options de réception
- [ ] **Module 1.3** : Communication du moyen de réception
- [ ] **Module 2.1** : Saisie du montant (mode "J'envoie")
- [ ] **Module 2.2** : Analyse et classement des options d'envoi
- [ ] **Module 2.3** : Action après le choix (guidage)
- [ ] **Module 4.2** : Notifications in-app (au moins une notification de bienvenue)
- [ ] **Module 5.1** : PWA installable

---

*Dernière mise à jour : 13 août 2026*
