<?php

/**
 * Génère un numéro de catalogue façon cote de bibliothèque, purement
 * présentationnel (dérivé de l'ID, aucune colonne dédiée en base).
 */
function catalog_no(string $prefix, int $id): string
{
    return 'NT-' . strtoupper($prefix) . str_pad((string) $id, 3, '0', STR_PAD_LEFT);
}

/**
 * Traduit une clé d'interface dans la langue courante du site.
 */
function t(string $key, ?string $default = null): string
{
    return \App\Core\Lang::t($key, $default);
}

/**
 * Échappe une chaîne pour affichage HTML sûr.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Construit une URL absolue à partir d'un chemin relatif.
 */
function url(string $path = ''): string
{
    $base = rtrim($GLOBALS['config']['app_url'] ?? '', '/');

    return $base . '/' . ltrim($path, '/');
}

/**
 * Construit une URL vers un asset statique (public/assets/...), avec un
 * paramètre de version basé sur la date de modification du fichier sur le
 * serveur. Sans ça, un navigateur qui a mis un .js/.css en cache continue
 * de le servir indéfiniment après un déploiement, même si le fichier a
 * changé côté serveur — l'URL ne changeant jamais, rien ne force le
 * navigateur à retélécharger. En ajoutant ?v=<date de modification>,
 * l'URL change automatiquement à chaque modification du fichier, ce qui
 * invalide le cache sans jamais avoir à y penser ni à vider son cache
 * manuellement.
 */
function asset(string $path): string
{
    $relativePath = ltrim($path, '/');
    $fullPath = dirname(__DIR__, 2) . '/public/assets/' . $relativePath;
    $version = is_file($fullPath) ? filemtime($fullPath) : time();

    return url('assets/' . $relativePath) . '?v=' . $version;
}
