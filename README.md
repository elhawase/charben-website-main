# CHARBEN INDUSTRIE — Build statique (déploiement Git Hostinger)

Ce dossier contient le **build de production prêt à servir** du site web
`charben-industrie.com`.

> ⚠️ **Important** : le Git générique Hostinger (hébergement Premium) **n'exécute
> aucun build**. Il copie les fichiers du dépôt tels quels sur `public_html`.
> C'est pourquoi ce dépôt contient le **build compilé**, et non le code source React.

## Contenu
- `index.html` — point d'entrée
- `assets/` — bundle JS + CSS (minifiés)
- `img/` — toutes les images du site (produits, équipe, logo, hero)
- `robots.txt`, `sitemap.xml` — référencement
- favicons + `apple-touch-icon.png`
- `RAPPORT-GLOBAL.md` — rapport global de tout le travail (déploiement, mobile,
  formulaire, Google) par Adjissi Hichem · workflow opencode

## Déploiement sur Hostinger (Premium)
1. Pousse ce dossier sur une branche d'un dépôt GitHub (ex. `main`).
2. Dans hPanel : **Websites → ton site → Advanced → Git**.
3. **Connect with GitHub** (OAuth) et choisis ce dépôt.
4. Branche : `main` · **Root/Deploy directory : `public_html`**.
5. **Deploy** — les fichiers sont copiés à la racine du site.

ℹ️ Après chaque modification, re-committer/pousser : le site se met à jour
automatiquement (déploiement auto).

## Notes backend
- Le site s'affiche **100 % en statique** sans backend.
- Le **formulaire de contact** envoie vers `/hcgi/platform` (PocketBase)
  — il ne reçoit les demandes que si le backend PocketBase est déployé sur
  l'hébergement sous cette URL.

## Réglages Google (référencement + mesure)

### SEO (déjà en place)
Le `index.html` contient :
- `meta description` (FR), `meta robots` (index, follow)
- `link canonical` → https://charben-industrie.com/
- Open Graph (title, description, url, image, locale) + Twitter card
- JSON-LD schema.org (`Organization`, adresse Medjana/DZ)

> ⚠️ L'`index.html` du déploiement contient aussi des **scripts Hostinger
> injectés** (journalisation d'erreurs vers l'éditeur). **Ne pas écraser** le
> fichier avec le `index.html` du build : insérer/modifier uniquement les
> métas dans `<head>`, en préservant ces scripts.

### Google Analytics (GA4)
- ID de mesure : **`G-QP8H9G6M94`**
- Balise gtag ajoutée dans le `<head>` de `index.html`
- Vérifié : le navigateur envoie bien un hit à
  `google-analytics.com/g/collect` (tid=G-QP8H9G6M94)
- Les données apparaissent dans Analytics sous 24-48 h
  (Rapports → Temps réel pour voir les visiteurs actifs)
- Pour modifier : changer l'ID dans l'`index.html` déployé **et** dans le
  source `apps/web/index.html` (pour les prochains builds)

### Google Search Console
- Propriété : `charben-industrie.com` (vérifiée par **DNS TXT** dans hPanel
  Hostinger → Domaines → Zone DNS)
- Sitemap soumis : `sitemap.xml` (public, HTTP 200)
- Console : https://search.google.com/search-console
- Les URLs soumises : `/`, `/a-propos`, `/contact`, `/legal`,
  `/mentions-legales`, `/politique-confidentialite`, `/politique-cookies`,
  `/politique-qse`
