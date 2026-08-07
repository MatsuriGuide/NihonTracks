# NihonTracks

Catalogue collaboratif de vidéos musicales japonaises (YouTube), avec fiches
artistes, tags et playlists. PHP natif / MySQL, sans framework, sans dépendance
Composer (compatible hébergement mutualisé sans SSH).

## Installation

1. Cloner le repo sur l'hébergement (ou déposer les fichiers via FTP/gestionnaire de fichiers).
2. Copier `.env.example` en `.env` et renseigner les identifiants de connexion à la base :
   ```
   DB_HOST=localhost
   DB_NAME=u320069492_NihonTracks
   DB_USER=...
   DB_PASS=...
   ```
3. Importer `docs/nihontracks-schema.sql` dans la base MySQL (phpMyAdmin ou équivalent).
4. Configurer le document root du (sous-)domaine sur le dossier `public/`.
   - Si ce n'est pas possible sur ton hébergement, garde le `.htaccess` à la racine
     du projet : il redirige automatiquement vers `public/`.
5. Vérifier que `storage/logs/` et `public/uploads/` sont accessibles en écriture (chmod 755/775).
6. Créer un compte via `/register`, puis le promouvoir admin manuellement en base
   (aucun compte n'est admin par défaut) :
   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'ton@email.com';
   ```
   Il faudra se déconnecter puis se reconnecter pour que le nouveau rôle soit pris
   en compte dans la session.

## Débogage

Passer `APP_ENV=local` dans `.env` affiche les erreurs PHP directement dans le
navigateur. À remettre en `production` une fois le problème résolu.
Les erreurs sont dans tous les cas journalisées dans `storage/logs/php-error.log`.

## Pas de Composer

`autoload.php` (racine) fait l'autoload PSR-4 du namespace `App\` à la main,
et `App\Core\Env` lit le fichier `.env` sans dépendance externe. `composer.json`
n'est présent qu'à titre indicatif (autocomplétion IDE en local) — il n'est
pas nécessaire de lancer `composer install`.

## Structure du projet

Voir `docs/architecture.md` pour le détail de l'arborescence, des conventions
et du système de rôles/permissions.

## État d'avancement

- [x] Schéma de base de données (`docs/nihontracks-schema.sql`)
- [x] Squelette applicatif (routing, DB, Auth, Lang, layout de base)
- [x] Page d'accueil (liste des vidéos publiées)
- [x] Dashboard admin + gestion des tags (lecture seule)
- [x] Authentification (register/login/logout, hashing bcrypt, session par rôle)
- [x] CRUD Artistes (création, édition, suppression, fiche publique)
- [x] CRUD Vidéos + intégration API YouTube (métadonnées auto, fallback manuel)
- [x] Attribution de tags (genre/langue) sur les vidéos — gestion des catégories/fusion encore à faire côté admin
- [x] Playlists (création, publique/privée, ajout/retrait de vidéos)
- [x] Modération (signalements, file de traitement, historique — rollback complet pas encore implémenté)
- [x] Traduction assistée par OpenAI (admin uniquement, EN/JA à partir du contenu FR)
- [x] Interface FR / EN / JA (contenu artistes/vidéos encore saisi en FR uniquement — EN/JA viendront via la traduction OpenAI)
