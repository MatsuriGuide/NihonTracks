# Cahier des charges — Site de suivi des sorties musicales japonaises

## Contexte
Site permettant de cataloguer les vidéos musicales (principalement YouTube) de groupes/artistes japonais suivis, avec fiches artistes et système de tags. Plateforme multi-utilisateurs avec modération a posteriori.

**Stack** : PHP / MySQL

---

## 1. Entités principales

### 1.1 Vidéos / Morceaux
- Lien YouTube (obligatoire, source principale)
- Titre du morceau
- Artiste(s) associé(s) — un morceau peut avoir plusieurs artistes (feat., collab)
- Date de sortie
- Miniature (auto-récupérée via API YouTube)
- Type : MV officiel, lyric video, live, performance video, cover, teaser...
- Tags (catégories fixes + libres)
- Ajouté par (utilisateur) / date d'ajout
- Statut : publié / signalé / masqué

### 1.2 Fiches artistes
- Nom (+ nom en japonais/romaji/kanji)
- Type : solo, groupe, duo...
- Membres (si groupe) — avec possibilité de lier vers d'autres fiches artistes si membre a une carrière solo
- Label / agence
- Genre(s) musical(aux)
- Année de début d'activité (+ fin si séparé)
- Réseaux sociaux / liens officiels
- Photo/avatar
- Description/bio
- Liste des morceaux associés (auto-générée depuis les vidéos liées)
- Tags

### 1.3 Tags
- **Catégories fixes** (structure imposée) : ex. Genre musical, Langue, Type de contenu, Label
- **Tags libres** : hashtags créés à la volée par les utilisateurs
- Un tag peut s'appliquer aux vidéos et/ou aux artistes

### 1.4 Utilisateurs
- Compte email/mot de passe
- Rôles : utilisateur standard / modérateur / admin
- Historique de contributions (vidéos/fiches/tags ajoutés ou modifiés)
- Possibilité de suivre des artistes (optionnel, à discuter)

---

## 2. Fonctionnalités

### 2.1 Ajout de contenu
- **Ajout manuel** : formulaire complet vidéo / artiste / tag
- **Ajout via API YouTube** : coller une URL → récupération auto (titre, miniature, date de publication, chaîne) puis complétion manuelle (artiste(s), tags, type)
- Détection de doublons (même URL déjà en base) avant ajout

### 2.2 Modération
- Tout ajout/modification est publié immédiatement
- Système de signalement par les utilisateurs (doublon, info erronée, spam, contenu inapproprié)
- File de signalements consultable par les modérateurs/admin
- Historique des modifications par fiche (qui a changé quoi, avec possibilité de rollback)

### 2.3 Navigation / recherche
- Liste des vidéos (filtrable par artiste, tag, date, type)
- Fiche artiste avec discographie/vidéographie complète
- Recherche globale (artiste, morceau, tag)
- Filtres combinés (ex. genre X + langue japonais + type MV)
- Tri par date de sortie / date d'ajout / popularité (vues YouTube si récupérées)

### 2.4 Comptes utilisateurs
- Inscription/connexion email + mot de passe
- Page profil avec historique de contributions
- Permissions différenciées (utilisateur / modérateur / admin)

---

## 3. Décisions prises

1. **Import initial** : pas de liste existante — remplissage manuel progressif par l'utilisateur.
2. **Playlists/collections** : oui, fonctionnalité incluse (playlists thématiques créées par les utilisateurs).
3. **Notifications** (alerte nouvelle vidéo d'un artiste suivi) : validée sur le principe, reportée en **phase 2**.
4. **Relations entre artistes** : oui — gestion des liens membre/groupe, carrière solo, anciens groupes, collaborations récurrentes (cf. 1.2).
5. **Multilingue** : interface en **FR / EN / JP**, avec traduction automatique via l'API OpenAI, fonctionnalité réservée aux administrateurs (déclenchement manuel de la traduction d'une fiche/vidéo, pas de traduction à la volée pour tous).
6. **Design** :
   - Phase 1 : design généraliste, orienté présentation d'artistes / catalogue musical (pas l'esthétique japonaise de koshiki.art).
   - Phase 2 (évolution future) : thème visuel personnalisable par page artiste/groupe.
7. **Quota API YouTube** : démarrage en tier gratuit de l'API YouTube Data v3, avec fallback en saisie manuelle si le quota journalier est atteint.

---

## 3bis. Fonctionnalités phase 2 (backlog, hors périmètre initial)

- Notifications de nouvelles sorties pour les artistes suivis
- Thèmes visuels personnalisés par fiche artiste
- Statistiques de popularité / vues agrégées

---

## 4. Architecture technique (proposition)

- **Backend** : PHP natif (cohérent avec koshiki.art), pattern MVC léger
- **Base de données** : MySQL
  - Tables principales : `users`, `artists`, `artist_relations` (membre/groupe, ex-groupe, collab), `videos`, `playlists`, `playlist_videos`, `tags`, `tag_categories`, `video_tags`, `artist_tags`, `video_artists` (relation N-N), `reports`, `edit_history`
  - Tables de traduction : `artists_i18n`, `videos_i18n` (une ligne par langue FR/EN/JP, remplies manuellement ou via traduction OpenAI déclenchée par un admin)
- **API externe** :
  - YouTube Data API v3 (métadonnées vidéo, tier gratuit + fallback saisie manuelle si quota atteint)
  - OpenAI API (traduction assistée, déclenchement manuel par un admin uniquement)
- **Authentification** : sessions PHP classiques + hash bcrypt
- **Internationalisation** : FR / EN / JP, sur le modèle `getSiteLangs()` déjà utilisé sur koshiki.art

---

*Document à valider et compléter avant démarrage du développement.*
