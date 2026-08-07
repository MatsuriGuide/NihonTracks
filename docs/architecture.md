# NihonTracks — Architecture du projet PHP

Structure MVC légère, dans la continuité des conventions déjà utilisées sur koshiki.art
(`Database::getInstance()`, `getSiteLangs()`, front controller unique).

## 1. Arborescence

```
nihontracks/
├── public/                        # DOCUMENT ROOT (seul dossier exposé au web)
│   ├── index.php                  # Front controller — point d'entrée unique
│   ├── .htaccess                  # Réécriture d'URL vers index.php
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   └── uploads/                   # Avatars artistes, cache miniatures (writable)
│
├── app/
│   ├── Config/
│   │   ├── config.php             # Config générale (nom du site, langues, etc.)
│   │   └── database.php           # Paramètres de connexion (lit le .env)
│   │
│   ├── Core/
│   │   ├── Database.php           # Singleton — Database::getInstance()
│   │   ├── Router.php             # Dispatch URL -> Controller::method
│   │   ├── Controller.php         # Classe de base (render, redirect, input...)
│   │   ├── View.php               # Moteur de rendu (layout + partials)
│   │   ├── Auth.php               # Session, login, rôles (user/moderator/admin)
│   │   └── Lang.php               # getSiteLangs(), t('key'), détection langue
│   │
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── ArtistController.php       # Fiche artiste, liste, recherche
│   │   ├── VideoController.php        # Ajout vidéo, détail, filtres
│   │   ├── TagController.php          # Navigation par tag
│   │   ├── PlaylistController.php
│   │   ├── AuthController.php         # Login / register / logout
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       ├── TagAdminController.php     # CRUD catégories + tags (cf. besoin identifié)
│   │       ├── ReportController.php       # File de modération
│   │       ├── UserAdminController.php
│   │       └── TranslationController.php  # Déclenchement traduction OpenAI
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Artist.php
│   │   ├── Video.php
│   │   ├── Tag.php
│   │   ├── TagCategory.php
│   │   ├── Playlist.php
│   │   ├── Report.php
│   │   └── EditHistory.php
│   │
│   ├── Services/
│   │   ├── YoutubeApiService.php      # Appel API YouTube Data v3 + fallback
│   │   ├── OpenAiTranslationService.php
│   │   ├── ModerationService.php      # Création/traitement des signalements
│   │   ├── EditHistoryService.php     # Écrit les diffs JSON à chaque modif
│   │   └── SlugService.php
│   │
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── main.php
│   │   │   └── admin.php
│   │   ├── home/
│   │   ├── artists/
│   │   ├── videos/
│   │   ├── tags/
│   │   ├── playlists/
│   │   ├── auth/
│   │   └── admin/
│   │
│   └── Helpers/
│       └── functions.php          # Helpers globaux (e(), url(), asset()...)
│
├── routes/
│   ├── web.php                    # Routes publiques
│   └── admin.php                  # Routes admin (préfixe /admin, middleware rôle)
│
├── storage/
│   ├── logs/
│   └── cache/
│
├── .env                           # Identifiants DB, clés API (hors Git)
├── .env.example
└── composer.json                  # Autoload PSR-4 + éventuelles libs (dotenv...)
```

## 2. Conventions reprises de koshiki.art

- **Accès DB** : toujours via `Database::getInstance()`, jamais de nouvelle connexion ad hoc.
- **Langues** : `Lang::getSiteLangs()` retourne `['fr', 'en', 'ja']` ; toute requête sur une table `_i18n` filtre sur la langue courante avec repli sur `fr` si la traduction n'existe pas.
- **Un seul front controller** (`public/index.php`) : toutes les requêtes passent par le `Router`, pas de fichiers PHP exécutables épars.
- **Séparation stricte public/admin** : `Admin\*Controller` protégés par un middleware de rôle (`Auth::requireRole('admin')` ou `'moderator'` selon l'action).

## 3. Flux d'une requête

```
Requête → public/index.php → Router → Controller → Model (Database::getInstance())
                                          ↓
                                        View (layout + partial) → HTML
```

## 4. Points d'architecture spécifiques à NihonTracks

| Besoin | Où |
|---|---|
| Ajout vidéo via URL YouTube | `VideoController::create()` → `YoutubeApiService::fetchMetadata()` → pré-remplissage formulaire |
| Détection doublon | `Video::findByYoutubeId()` avant insertion |
| Signalement | Bouton sur vue/fiche → `ModerationService::report()` → table `reports` |
| Historique/rollback | Chaque `update()`/`delete()` de Model passe par `EditHistoryService::log()` |
| Traduction OpenAI (admin) | `Admin\TranslationController` → `OpenAiTranslationService` → écrit dans `*_i18n` avec `is_auto_translated = 1` |
| Gestion tags/catégories | `Admin\TagAdminController` (CRUD + fusion de doublons) |

## 6. Rôles et permissions

Trois rôles stockés dans `users.role` : `user`, `moderator`, `admin`.

| Action | Utilisateur | Modérateur | Admin |
|---|---|---|---|
| Consulter le site (public) | ✅ | ✅ | ✅ |
| Ajouter vidéo / fiche artiste / tag | ✅ | ✅ | ✅ |
| Modifier **son propre** contenu | ✅ | ✅ | ✅ |
| Modifier le contenu d'un autre utilisateur **hors signalement** | ❌ | ❌ | ✅ |
| Signaler un contenu | ✅ | ✅ | ✅ |
| Traiter un signalement (et à cette occasion seulement : corriger/masquer/supprimer le contenu signalé) | ❌ | ✅ | ✅ |
| Créer des playlists | ✅ | ✅ | ✅ |
| Gérer les catégories/tags (CRUD, fusion doublons) | ❌ | ❌ | ✅ |
| Déclencher une traduction OpenAI | ❌ | ❌ | ✅ |
| Gérer les comptes utilisateurs (promotion modérateur, désactivation) | ❌ | ❌ | ✅ |
| Voir l'historique de modification (`edit_history`) | Son propre historique | Tout | Tout |

**Promotion modérateur** : manuelle uniquement, par un admin, depuis `Admin\UserAdminController`. Pas d'auto-promotion.

**Règle d'implémentation clé** : un modérateur ne peut agir sur le contenu d'autrui **que depuis l'écran de traitement d'un signalement** (`Admin\ReportController::resolve()`). Concrètement :
- `ModerationService::resolve($reportId, $action)` est le seul point d'entrée qui autorise une modification cross-utilisateur par un modérateur.
- Les méthodes `update()`/`delete()` des Models exposées aux Controllers publics (`ArtistController`, `VideoController`...) vérifient toujours `content.created_by === currentUser.id OR currentUser.role === 'admin'` — le modérateur n'y est **pas** inclus.
- Chaque résolution de signalement passe aussi par `EditHistoryService::log()`, donc traçable et "rollbackable" comme n'importe quelle autre modification.

Structure de session (`Auth`) :
```php
$_SESSION['user'] = [
    'id'    => 12,
    'role'  => 'moderator',   // user | moderator | admin
    'lang'  => 'fr',
];
```
`Auth::requireRole('admin')` / `Auth::requireRole(['moderator','admin'])` en tête de méthode de Controller, ou middleware appliqué au niveau du `Router` pour tout le préfixe `/admin`.

## 7. Prochaines étapes possibles

1. Squelette de fichiers (Core/Database.php, Router.php, index.php, .env.example)
2. Premier Controller + Model fonctionnels (ex. `ArtistController` en lecture seule)
3. Système d'authentification (`AuthController`, hashing bcrypt, sessions, rôles)
