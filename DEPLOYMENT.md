# 🚀 Guide de Déploiement Gratuit — Momo Optimizer

Ce guide explique étape par étape comment déployer votre SaaS Momo Optimizer gratuitement et sans carte bancaire requise.

> **Statut de la base Supabase** : ✅ Active et tables `sessions`/`cache` migrées avec succès.

---

## Choix n°1 : Déploiement sur Koyeb (Recommandé ⭐)

**Pourquoi Koyeb ?**
- Instance Eco Nano **100% gratuite**.
- **Ne s'endort jamais** : Votre application tourne 24h/24 sans délai de réveil.
- Détection et build Docker automatiques depuis GitHub.
- Certificat SSL HTTPS inclus gratuitement.

### Étapes de déploiement Koyeb :

1. **Créer un compte** :
   - Rendez-vous sur [koyeb.com](https://www.koyeb.com) et connectez-vous avec votre compte GitHub.

2. **Créer un nouveau service** :
   - Cliquez sur **"Create App"** ou **"Create Service"**.
   - Sélectionnez la source : **GitHub**.
   - Choisissez votre dépôt : `eliletishbit/momo-optimizer`.
   - Sélectionnez la branche : `main`.

3. **Configuration du Build** :
   - Koyeb détectera automatiquement le `Dockerfile` à la racine.
   - Laissez la méthode sur **Dockerfile**.

4. **Variables d'environnement (Environment Variables)** :
   Ajoutez les variables suivantes dans la section *Environment variables* :
   - `APP_NAME` = `MomoOpti`
   - `APP_ENV` = `production`
   - `APP_DEBUG` = `false`
   - `APP_KEY` = `base64:OvM0OFN95GidHUshSKi7xCa/6iZp0GTLT5BYm7c9W34=`
   - `APP_URL` = `https://<nom-de-votre-app>.koyeb.app` (vous pouvez ajuster une fois le domaine généré)
   - `DB_CONNECTION` = `pgsql`
   - `DB_HOST` = `aws-0-eu-central-1.pooler.supabase.com`
   - `DB_PORT` = `6543`
   - `DB_DATABASE` = `postgres`
   - `DB_USERNAME` = `postgres.acvwzwjoikyjvpoalxdh`
   - `DB_PASSWORD` = `<votre-mot-de-passe-supabase>`
   - `DB_SSLMODE` = `require`

5. **Exposition des Ports** :
   - Port : `80` ou `8000` (protocole HTTP). Koyeb redirige automatiquement le trafic HTTPS.

6. **Déployer** :
   - Cliquez sur **"Deploy"**.
   - Koyeb va cloner le code, installer les dépendances Composer & NPM, compiler les assets Tailwind/Vite, exécuter les migrations et démarrer l'application.

---

## Choix n°2 : Déploiement sur Render

1. Rendez-vous sur [render.com](https://render.com) et connectez-vous avec GitHub.
2. Cliquez sur **New +** > **Web Service**.
3. Liez le dépôt `eliletishbit/momo-optimizer`.
4. Choisissez l'environnement **Docker**.
5. Sélectionnez le plan **Free**.
6. Renseignez les variables d'environnement listées ci-dessus.
7. Cliquez sur **Create Web Service**.

---

## 🔄 Flux de Travail Git (Workflow Développeur Pro)

Pour maintenir la branche `main` stable et fonctionnelle à tout moment :

1. **Travailler sur une sous-branche pour chaque fonctionnalité** :
   ```bash
   git checkout develop
   git checkout -b feature/nouvelle-fonctionnalite
   ```

2. **Tester et commiter vos modifications** :
   ```bash
   git add .
   git commit -m "feat: description de la modification"
   ```

3. **Fusionner dans `develop`** :
   ```bash
   git checkout develop
   git merge feature/nouvelle-fonctionnalite
   ```

4. **Déployer en production via `main`** :
   ```bash
   git checkout main
   git merge develop
   git push origin main
   ```
   *Dès que vous poussez sur `main`, Koyeb / Render redéploie automatiquement la nouvelle version sans interruption !*
