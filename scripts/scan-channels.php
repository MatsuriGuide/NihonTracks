<?php

/**
 * Script de surveillance des chaînes YouTube liées aux artistes.
 *
 * À exécuter en ligne de commande (CRON Hostinger), PAS accessible via le web
 * puisqu'il vit hors du dossier public/. Exemple de commande CRON (toutes les 4h) :
 *   0 star-slash-4 * * * php /home/xxx/domains/koshiki.art/public_html/nihontracks/scripts/scan-channels.php
 * (remplacer star-slash-4 par l'expression cron réelle "*\/4")
 *
 * Coût API : 3 unités par artiste surveillé (channels.list + playlistItems.list
 * + videos.list groupé pour le filtre anti-Shorts), jusqu'à 50 vidéos examinées.
 *
 * Chaque exécution (réussie, en erreur, ou tentative d'accès web bloquée) est
 * journalisée dans la table scan_log, consultable depuis /admin/watch.
 */

declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';

use App\Core\Database;
use App\Core\Env;
use App\Models\ScanLog;
use App\Services\ChannelWatcherService;
use App\Services\YoutubeApiService;

Env::load(dirname(__DIR__) . '/.env');

if (PHP_SAPI !== 'cli') {
    http_response_code(403);

    try {
        ScanLog::recordBlockedAccess(
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );
    } catch (\Throwable $e) {
        // Le 403 doit s'appliquer même si le log échoue (ex: DB indisponible)
    }

    exit('Ce script ne peut être exécuté qu\'en ligne de commande.');
}

$db = Database::getInstance();

// Un seul artiste peut avoir plusieurs liens YouTube (cas d'une chaîne "topic"
// distincte, cf. artist_links) : on surveille chacun d'eux séparément.
$links = $db->fetchAll(
    'SELECT artist_id, url FROM artist_links WHERE platform = "youtube"'
);

$scanned = 0;
$found = 0;
$errors = 0;
$errorDetails = [];

foreach ($links as $link) {
    $channelId = YoutubeApiService::extractChannelId($link['url']);

    if ($channelId === null) {
        // Lien pas au format /channel/UC..., pas de détection possible pour celui-ci
        continue;
    }

    $result = ChannelWatcherService::scanArtist((int) $link['artist_id'], $channelId);

    if ($result === null) {
        $errors++;
        $errorDetails[] = ['artist_id' => (int) $link['artist_id'], 'channel_id' => $channelId];

        continue;
    }

    $scanned++;
    $found += $result;
}

ScanLog::record('cron', $scanned, $found, $errors, $errorDetails ?: null);

echo sprintf(
    "[%s] Scan terminé : %d chaîne(s) analysée(s), %d nouvelle(s) suggestion(s), %d erreur(s).\n",
    date('Y-m-d H:i:s'),
    $scanned,
    $found,
    $errors
);
