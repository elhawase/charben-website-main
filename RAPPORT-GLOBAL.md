# 📊 RAPPORT GLOBAL — Site CHARBEN INDUSTRIE

> **Rédigé par** : Adjissi Hichem · **Workflow** : opencode
> **Projet** : https://charben-industrie.com · **Repo** : `elhawase/charben-industrie` (privé)
> **Période** : 29 / 08 / 2026 → 31 / 08 / 2026 · **80 commits** (branche `main`)

---

## 🎯 Résumé exécutif

Déploiement complet et maintenance du site vitrine **CHArben INDUSTRIE** (scierie / menuiserie industrielle, Medjana, Algérie), construit en **React + Vite**, hébergé sur **Hostinger** via déploiement Git automatique.

Le travail couvre **4 grands volets** :
1. **Déploiement & hébergement** — infrastructure SPA fonctionnelle sur Hostinger.
2. **Refonte & adaptation mobile** — sans jamais toucher à la version PC (contrainte clé).
3. **Formulaire de contact / demandes de devis** — envoi par email fiable (SMTP).
4. **Référencement & mesure Google** — SEO, Google Analytics, Search Console.

---

## 1️⃣ Déploiement & hébergement

| Élément | Détail |
|---|---|
| **Stack** | React + Vite (build statique) |
| **Hébergement** | Hostinger Premium — Git auto-deploy (copie du build sur `public_html`) |
| **Repo** | `elhawase/charben-industrie` (privé), branche `main` |
| **Routage SPA** | `.htaccess` → toutes les routes (`/a-propos`, `/contact`, `/legal`, …) vers `index.html` ; protection `/hcgi` |
| **Optimisation images** | Hero & vitrine 2048→1600 px, poids 468 Ko → 195 Ko ; suppression de 29 images inutilisées (27,7 Mo → 1,9 Mo) |
| **Favicons** | icônes multi-formats : 16/32/48 .png, .ico, apple-touch-icon |

---

## 2️⃣ Refonte & adaptation mobile (en préservant le PC)

> **Contrainte respectée** : aucune modification des classes `lg:*` / du bloc desktop sans accord explicite.

### Carousel mobile « Infrastructure »
- **Effet cinématique** Ken Burns (zoom continu) + parallaxe + inclinaison 3D.
- **Défilement épingle** : 1 geste = 1 carte pleine écran ; retour à l'accrochage fiable (unité `dvh` unifiée).
- Cartes sans n° ni libellé, textes agrandis, boutons « une touche ».
- **Autoplay affiné itérativement** (8 itérations testées) → version finale : 1ère transition rapide (~1,5 s) puis `9200 ms` entre cartes, déclenché quand la 1ère carte est ≥ 75 % visible.
- 6 cartes : Sourcing → Transport → Scierie → Séchage → Marché → Bio.

### Héro mobile
- Bannière à la façon « Nat Geo » avec masque blanc dégradé, parallax + fondu au scroll.
- Titre sur 2 lignes le plus lisible : **mobile 40/24 px, desktop 96/72 px** ; retrait de l'eyebrow (localisation) et du `text-justify` (espaces disgracieux).

### Footer / Réseaux sociaux
- Icônes **Facebook, Instagram, LinkedIn, X, YouTube, TikTok** alignées sur une seule ligne (44 px), toutes versions.
- **Logo** avec effet agrandir/réduire automatique (mobile & PC).

### Fenêtre « Catalogue »
- Navigation plein écran avec onglets **Catalogue / FAQ / Demande de devis** (desktop + mobile).
- Refonte style **Harvesta** (palette claire, tables & cartes épurées), retrait du formulaire de devis (infos seules + CTA vers la page contact), catalogue + FAQ en un seul défilement.

### Sections / contenus
- Nouvelles sections **« Nos valeurs »** (3 colonnes) + **FAQ accordéon**.
- Carte **Marché** initialement retirée puis **restaurée** (décision utilisateur) ; suppression de la mention « (Marseille, Barcelone) » FR + EN.
- Suppression du bandeau défilant (ticker) et des compteurs du hero.

---

## 3️⃣ Formulaire de contact / demandes de devis

| Élément | Détail |
|---|---|
| **Formulaire de devis** | ajouté sur la page `/contact` |
| **Envoi** | `send-form.php` → **SMTP Hostinger (port 465)** avec **repli automatique sur `mail()`** si SMTP échoue |
| **Fiabilité** | creds SMTP mis à jour ; test de diagnostic puis suppression de la sonde |
| **Historique** | d'abord `mailto:` → puis backend PocketBase → finalement SMTP fiable |

---

## 4️⃣ Référencement & mesure Google

### SEO (en place, en ligne)
- `meta description` (FR), `meta robots` (index, follow)
- `link canonical` → https://charben-industrie.com/
- **Open Graph** (title, description, url, image, locale) + Twitter card
- **JSON-LD** schema.org (Organization, adresse Medjana / DZ)
- `robots.txt` → référence `sitemap.xml` (public, HTTP 200)
- `sitemap.xml` → 8 URLs soumises

> ⚠️ L'`index.html` déployé contient des **scripts Hostinger injectés** (journalisation d'erreurs). On modifie uniquement les métas dans `<head>` en **préservant** ces scripts (l'`index.html` du build ne doit pas l'écraser).

### Google Analytics (GA4)
- **ID de mesure : `G-QP8H9G6M94`**
- Balise **gtag** ajoutée dans le `<head>` (déployé + source).
- **Vérifié par test navigateur** : hit réel envoyé à `google-analytics.com/g/collect` (tid=G-QP8H9G6M94).
- Premières données visibles dans **Rapports → Temps réel** sous 24–48 h.

### Google Search Console
- Propriété **`charben-industrie.com`** créée et **vérifiée par DNS TXT** (hPanel Hostinger).
- **Sitemap soumis** : `sitemap.xml` → « Sitemap envoyé ».
- Indexation en cours par Google.

---

## 🧪 Vérifications & garanties qualité

**Vérification générale finale (Playwright, mobile 380 px + desktop 1280 px) :**
- ✅ **Aucune erreur** console / page / réseau
- ✅ **Aucun débordement horizontal** (mobile & desktop)
- ✅ Hero : mobile **40 px** / desktop **96 px**, visibles
- ✅ Carousel mobile présent ; **autoplay fonctionnel** (~5,5 s après arrivée sur la section)
- ✅ Accordéon desktop visible (desktop carousel caché)
- ✅ **Toutes les images chargent** (les « images cassées » initiales = faux positif : carousel `lg:hidden` + lazy-load)

---

## 📦 Livrables

| Fichier clé | Rôle |
|---|---|
| `index.html` | Entrée SPA + métas SEO + tag gtag + scripts Hostinger |
| `assets/` | Bundle JS + CSS minifiés |
| `img/` | Images optimisées du site |
| `.htaccess` | Routage SPA + protection backend |
| `robots.txt`, `sitemap.xml` | Référencement |
| `send-form.php` | Envoi des devis par SMTP |
| `favicon*`, `apple-touch-icon.png` | Icônes |
| `README.md` | Documentation build + réglages Google |

---

## 🚀 Pour aller plus loin (suggestions)

- **Google Analytics** : vérifier les premiers visiteurs dans *Rapports → Temps réel* après 24–48 h.
- **Performance** : audit Lighthouse (CSS ~97 Ko, JS ~505 Ko — gzip ~3,5/16/163 Ko).
- **Multilingue** : site déjà FR/EN ; penser au `hreflang` pour l'indexation bilingue.
- **Backend**: le formulaire passe par SMTP `send-form.php` ; le backend PocketBase (`/hcgi/platform`) reste optionnel.

---

*Rapport généré automatiquement le 31/08/2026 · 80 commits sur `main` · workflow opencode.*
