<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Router;

// Charge les variables d'environnement (.env)
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

session_start();

// Config globale disponible partout via $GLOBALS['config'] si besoin
$GLOBALS['config'] = require dirname(__DIR__) . '/app/Config/config.php';

require dirname(__DIR__) . '/app/Helpers/functions.php';

// Déclaration des routes
$router = new Router();
require dirname(__DIR__) . '/routes/web.php';
require dirname(__DIR__) . '/routes/admin.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
