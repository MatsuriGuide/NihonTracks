<?php

/**
 * Autoloader minimaliste pour le namespace App\.
 * Remplace vendor/autoload.php, inutile ici puisque Composer
 * n'est pas disponible sur l'hébergement mutualisé.
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/app/' . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
