<?php

declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';

use App\Core\Env;
use App\Core\Router;

// Charge les variables d'environnement (.env)
Env::load(dirname(__DIR__) . '/.env');

// Mode debug : n'affiche les erreurs PHP que si APP_ENV=local dans le .env
if (($_ENV['APP_ENV'] ?? 'production') === 'local') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}

// Toutes les erreurs (affichées ou non) sont journalisées ici
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/storage/logs/php-error.log');

session_start();

// Config globale disponible partout via $GLOBALS['config'] si besoin
$GLOBALS['config'] = require dirname(__DIR__) . '/app/Config/config.php';

require dirname(__DIR__) . '/app/Helpers/functions.php';

// Déclaration des routes
$router = new Router();
require dirname(__DIR__) . '/routes/web.php';
require dirname(__DIR__) . '/routes/admin.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
