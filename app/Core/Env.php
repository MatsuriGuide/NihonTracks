<?php

namespace App\Core;

/**
 * Chargement minimaliste d'un fichier .env, sans dépendance externe.
 * Remplace vlucas/phpdotenv, inutilisable sans Composer sur l'hébergement mutualisé.
 *
 * Compatible PHP 7.4+ : le PHP utilisé en ligne de commande sur certains
 * hébergements mutualisés (CRON) peut être plus ancien que le PHP qui sert
 * les pages web, donc pas de str_starts_with()/str_contains() ici (PHP 8.0+).
 */
class Env
{
    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Ignore les lignes vides et les commentaires
            if ($line === '' || substr($line, 0, 1) === '#') {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            $value = trim($value);

            // Retire d'éventuels guillemets autour de la valeur
            $value = trim($value, "\"'");

            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}
