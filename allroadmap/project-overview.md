# Project Overview : MomoOpti

## 📋 Informations Générales

| Champ | Valeur |
|-------|--------|
| **Nom du projet** | MomoOpti |
| **Version** | 1.0 (MVP) |
| **Statut** | En développement |
| **Date de création** | Août 2026 |
| **Type** | SaaS Web PWA |
| **Marché cible** | Bénin (extension possible vers Afrique de l'Ouest, Afrique, puis monde) |

---

## 🎯 Le Problème

### Le constat

Chaque jour, des millions d'Africains envoient et reçoivent de l'argent via des réseaux Mobile Money, des banques, ou d'autres moyens de paiement. Pourtant :

1. **Les frais sont opaques et variables** : Les utilisateurs ne savent jamais combien ils vont payer avant d'effectuer la transaction.
2. **Les grilles tarifaires sont complexes** : Chaque opérateur a ses propres paliers, ses propres règles, ses propres frais.
3. **L'utilisateur ne connaît que son réseau** : La plupart des gens utilisent le réseau qu'ils connaissent, sans savoir s'il est le moins cher.
4. **Les frais d'envoi et de retrait sont confondus** : Les gens ne font pas la distinction entre ce qu'ils paient pour envoyer et ce qu'ils paient pour recevoir.
5. **Le contexte change selon les pays** : Un Béninois qui doit recevoir de l'argent de France, du Sénégal ou de Côte d'Ivoire n'a pas les mêmes options.

### Le cas concret
Fatima, une commerçante à Cotonou, doit recevoir 175 000 FCFA de son frère.
Son frère lui demande : "Sur quel réseau je t'envoie l'argent ?"
Fatima ne sait pas. Elle répond : "Mets-moi sur MTN, c'est ce que j'utilise."
Elle paie 2 000 FCFA de frais de retrait (MTN).
Mais si elle avait choisi Moov, elle aurait payé 1 750 FCFA.
Soit une perte de 250 FCFA (14% du montant des frais).

Pire : si elle avait utilisé une combinaison Moov (100 000) + Celtiis (75 000),
elle aurait payé 1 900 FCFA, soit 300 FCFA de moins qu'avec MTN seul.

text

### Le problème systémique

> **"Je ne sais jamais combien je vais payer pour envoyer ou recevoir de l'argent, et je ne sais pas quel réseau choisir pour minimiser mes frais."**

C'est un problème de **transparence**, de **comparaison** et d'**optimisation personnalisée**.

---

## 💡 La Solution : MomoOpti

### Concept

**MomoOpti est un assistant personnel d'optimisation des frais d'envoi et de retrait d'argent.**

L'application permet à chaque utilisateur de :
1. **Enregistrer ses moyens de paiement préférés** (MTN, Moov, Celtiis, banque, etc.)
2. **Pour chaque transaction, obtenir un classement clair** des frais pour ses moyens enregistrés
3. **Choisir la meilleure option** en fonction du montant et du contexte
4. **Communiquer facilement** le moyen de réception à l'envoyeur

### Pourquoi c'est différent

| Problème | Solution MomoOpti |
|----------|-------------------|
| Frais opaques | Affichage transparent des frais pour chaque moyen |
| Choix par défaut | Classement des options par coût croissant |
| Pas de comparaison | Comparaison instantanée de tous les moyens enregistrés |
| Frais d'envoi et retrait mélangés | Deux modes distincts : "Je reçois" / "J'envoie" |
| Contexte local uniquement | Extension possible à tous les pays du monde |

### Promesse de valeur

> **"MomoOpti vous dit exactement combien vous allez payer pour envoyer ou recevoir de l'argent, et vous propose la solution la moins chère parmi vos moyens préférés."**

---

## 👥 Public Cible

### 1. Particuliers (B2C)
- **Profil** : Toute personne qui envoie ou reçoit de l'argent régulièrement
- **Problème** : Perd de l'argent sans le savoir, ne sait pas comparer
- **Utilisation** : 1 à 5 transactions par semaine
- **Volonté de payer** : Faible à moyenne (1 500 FCFA/mois max)

### 2. Commerçants (B2C/B2B)
- **Profil** : Petits commerçants, boutiquiers, vendeurs qui reçoivent et envoient de l'argent quotidiennement
- **Problème** : Multiples transactions → pertes importantes
- **Utilisation** : 5 à 20 transactions par jour
- **Volonté de payer** : Élevée (5 000 à 10 000 FCFA/mois)

### 3. PME et Entreprises (B2B)
- **Profil** : PME, startups, entreprises avec des employés
- **Problème** : Gestion des salaires, paiements fournisseurs, encaissements clients
- **Utilisation** : 20 à 100 transactions par mois
- **Volonté de payer** : Très élevée (25 000 à 50 000 FCFA/mois)

### 4. Freelances et Travailleurs indépendants
- **Profil** : Graphistes, développeurs, consultants qui reçoivent des paiements de l'étranger
- **Problème** : Frais de conversion, frais de retrait
- **Utilisation** : 2 à 10 transactions par mois
- **Volonté de payer** : Moyenne (2 500 à 5 000 FCFA/mois)

### 5. Utilisateurs internationaux (extension future)
- **Profil** : Africains de la diaspora, expatriés, entreprises multinationales
- **Problème** : Transferts internationaux, conversion de devises
- **Utilisation** : Variables
- **Volonté de payer** : Élevée

---

## 📊 Cas d'Usage Concrets (UX détaillée)

### Cas 1 : "Je reçois de l'argent" (Fatima, commerçante)
Fatima ouvre MomoOpti

Elle sélectionne le mode "Je reçois"

Elle tape le montant : 175 000 FCFA

L'appli affiche :
📊 Classement des options de réception :
🥇 1. Moov : 1 750 FCFA (le moins cher)
🥈 2. MTN : 2 000 FCFA (+250 FCFA)
🥉 3. Celtiis : 2 000 FCFA (+250 FCFA)
💡 Option combinée (juste pour info) :

Moov (100 000) + Celtiis (75 000) = 1 900 FCFA

Fatima choisit Moov

L'appli lui génère un message à envoyer à son frère :
"Envoie-moi 175 000 FCFA sur Moov. Mon numéro est le [numéro]."

Son frère envoie l'argent sur Moov

Fatima reçoit 175 000 - 1 750 = 173 250 FCFA

text

### Cas 2 : "J'envoie de l'argent" (Jean, freelance)
Jean ouvre MomoOpti

Il sélectionne le mode "J'envoie"

Il tape le montant : 100 000 FCFA

L'appli affiche :
📊 Classement des options d'envoi :
🥇 1. MTN : 125 FCFA (le moins cher)
🥈 2. Celtiis : 500 FCFA (+375 FCFA)
🥉 3. Moov : 1 000 FCFA (+875 FCFA)

Jean choisit MTN

Il va dans l'appli MTN MoMo et envoie 100 000 FCFA sur le réseau indiqué par le destinataire

Jean paie 125 FCFA de frais d'envoi

text

### Cas 3 : "Je voyage" (extension internationale)
Amadou, un Béninois, reçoit de l'argent de France

Il enregistre ses moyens de réception : MTN (Bénin), Orange Money (Sénégal), compte Wise (international)

Il tape le montant en Euros (200€)

L'appli calcule :

Wise : 2€ de frais de conversion

Orange Money : 5€ de frais

MTN Bénin : 8€ de frais

Amadou choisit Wise

Il communique son compte Wise à l'expéditeur

text

---

## 💰 Modèle Économique

### Stratégie Freemium (Gratuit + Payant)

| Niveau | Prix | Fonctionnalités | Cible |
|--------|------|-----------------|-------|
| **Essai 7 jours** | 0 FCFA | Toutes les fonctionnalités Premium | Tous |
| **Gratuit** | 0 FCFA | 5 analyses/jour, 2 moyens enregistrés | Particuliers occasionnels |
| **Premium** | 1 500 FCFA/mois | Analyses illimitées, moyens illimités, historique | Commerçants, freelances |
| **Pro** | 5 000 FCFA/mois | API, intégration, optimisation avancée | PME, boutiques |
| **Business** | 10 000 FCFA/mois | Support dédié, multi-comptes, dashboard | Grandes entreprises |
| **International** | À définir | Extension à d'autres pays, devises | Utilisateurs internationaux |

### Projection financière (6 mois, cible Bénin)

| Mois | Utilisateurs gratuits | Abonnés Premium | Revenus (FCFA) |
|------|----------------------|-----------------|----------------|
| 1 | 500 | 10 | 15 000 |
| 2 | 1 500 | 30 | 45 000 |
| 3 | 3 000 | 60 | 90 000 |
| 4 | 5 000 | 100 | 150 000 |
| 5 | 8 000 | 150 | 225 000 |
| 6 | 12 000 | 200 | 300 000 |

**Seuil de rentabilité** : 70 abonnés Premium (105 000 FCFA/mois)

---

## 🏗️ Stack Technologique

| Couche | Technologie | Version | Justification |
|--------|-------------|---------|---------------|
| **Framework** | Laravel | 11/12 | PHP, productif, écosystème riche |
| **Frontend (UI)** | Blade + Tailwind CSS | 3.x | Rendu SSR, SEO, rapidité |
| **Interactivité** | Livewire 3 + Alpine.js 3 | 3.x | Interactivité asynchrone sans JS complexe |
| **Base de données** | PostgreSQL (via Supabase) | 15+ | Performant, managé, plan Free |
| **PWA** | Configuration Laravel PWA | - | Application installable sur mobile |
| **Paiements** | FedaPay | - | Mobile Money, cartes, local |
| **Déploiement** | Vercel / DigitalOcean | - | Automatisé, scalable |
| **Versioning** | Git + GitHub | - | Gestion de versions |

---

## 🚀 Roadmap (Versioning)

### V1.0 (MVP) - Septembre 2026
- ✅ Authentification (Laravel Breeze)
- ✅ Configuration des moyens de paiement préférés (MTN, Moov, Celtiis)
- ✅ Mode "Je reçois" : analyse et classement des frais de retrait
- ✅ Mode "J'envoie" : analyse et classement des frais d'envoi
- ✅ PWA installable
- ✅ Intégration FedaPay (paiements)
- ✅ Plan Gratuit et Premium

### V1.1 - Octobre 2026
- ✅ Historique des transactions
- ✅ Classements multi-réseaux (combinaisons)
- ✅ Alertes de baisse de frais
- ✅ Export des résultats (PDF, partage)

### V1.2 - Novembre 2026
- ✅ Intégration avec Mobile Money (API opérateurs)
- ✅ Transfert direct via l'application
- ✅ Notifications push

### V2.0 - Décembre 2026
- ✅ Extension à la Côte d'Ivoire
- ✅ Ajout de nouveaux réseaux (Orange Money, etc.)
- ✅ Mode "Je voyage" (plusieurs pays)

### V3.0 - Mars 2027
- ✅ Extension à l'Afrique de l'Ouest (Sénégal, Togo, Ghana)
- ✅ API publique pour développeurs
- ✅ Marketplace d'intégrations

---

## 🧠 Ce qui rend MomoOpti unique

| Caractéristique | Avantage concurrentiel |
|-----------------|------------------------|
| **Personnalisation** | L'utilisateur configure ses moyens préférés, pas de comparaison inutile |
| **Simplicité** | Deux modes : "Je reçois" / "J'envoie" → zéro confusion |
| **Transparence** | Frais affichés clairement, classement par coût |
| **Actionnable** | L'utilisateur sait exactement quoi faire après l'analyse |
| **Multi-réseaux** | MTN, Moov, Celtiis, banques, et plus |
| **Multi-pays** | Conçu pour s'étendre au monde entier |
| **PWA** | Installable sur mobile, accessible hors ligne |

---

## 📈 Indicateurs de Performance (KPIs)

| KPI | Objectif (V1) | Objectif (V6) |
|-----|---------------|---------------|
| **Utilisateurs actifs (DAU)** | 500 | 50 000 |
| **Taux de conversion (Gratuit → Payant)** | 5% | 15% |
| **Revenu mensuel récurrent (MRR)** | 150 000 FCFA | 30 000 000 FCFA |
| **Économie moyenne par utilisateur** | 500 FCFA/mois | 2 500 FCFA/mois |
| **Score NPS (Satisfaction)** | 50 | 70 |
| **Temps de chargement (PWA)** | < 2 secondes | < 1 seconde |

---

## ✍️ Auteur

- **Nom** : [À compléter]
- **Email** : [À compléter]
- **Site web** : [À compléter]
- **Date** : 13 août 2026

---

**Ce document est un document de référence. Toute modification doit être validée par le chef de projet.** 🚀