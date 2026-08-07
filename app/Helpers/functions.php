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
 * Construit une URL vers un asset statique (public/assets/...).
 */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}
