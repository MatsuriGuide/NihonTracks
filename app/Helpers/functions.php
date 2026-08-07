<?php

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
