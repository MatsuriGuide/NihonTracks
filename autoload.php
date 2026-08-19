<?php

/**
 * Autoloader minimaliste pour le namespace App\.
 * Remplace vendor/autoload.php, inutile ici puisque Composer
 * n'est pas disponible sur l'hébergement mutualisé.
 *
 * Compatible PHP 7.4+ : le PHP utilisé en ligne de commande sur certains
 * hébergements mutualisés (CRON) peut être plus ancien que le PHP qui sert
 * les pages web, donc pas de str_starts_with() ici (PHP 8.0+).
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';

    if (substr($class, 0, strlen($prefix)) !== $prefix) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/app/' . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
