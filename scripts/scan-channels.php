<?php

/**
 * Script de surveillance des chaînes YouTube liées aux artistes.
 *
 * À exécuter en ligne de commande (CRON Hostinger), PAS accessible via le web
 * puisqu'il vit hors du dossier public/. Exemple de commande CRON (toutes les 6h) :
 *   0 star-slash-6 * * * php /home/xxx/domains/koshiki.art/public_html/nihontracks/scripts/scan-channels.php
 * (remplacer star-slash-6 par l'expression cron réelle "*\/6")
 *
 * Coût API : 2 unités par artiste surveillé (channels.list + playlistItems.list).
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Ce script ne peut être exécuté qu\'en ligne de commande.');
}

require dirname(__DIR__) . '/autoload.php';

use App\Core\Database;
use App\Core\Env;
use App\Models\VideoSuggestion;
use App\Services\YoutubeApiService;

Env::load(dirname(__DIR__) . '/.env');

$db = Database::getInstance();

// Un seul artiste peut avoir plusieurs liens YouTube (cas d'une chaîne "topic"
// distincte, cf. artist_links) : on surveille chacun d'eux séparément.
$links = $db->fetchAll(
    'SELECT artist_id, url FROM artist_links WHERE platform = "youtube"'
);

$scanned = 0;
$found = 0;
$errors = 0;

foreach ($links as $link) {
    $channelId = YoutubeApiService::extractChannelId($link['url']);

    if ($channelId === null) {
        // Lien pas au format /channel/UC..., pas de détection possible pour celui-ci
        continue;
    }

    $artistId = (int) $link['artist_id'];

    $uploadsPlaylistId = YoutubeApiService::fetchUploadsPlaylistId($channelId);

    if ($uploadsPlaylistId === null) {
        $errors++;

        continue;
    }

    $videos = YoutubeApiService::fetchPlaylistVideos($uploadsPlaylistId, 10);
    $scanned++;

    foreach ($videos as $video) {
        if (VideoSuggestion::youtubeIdKnown($video['youtube_id'])) {
            // Déjà en base (vidéo existante OU suggestion déjà traitée,
            // y compris rejetée) : jamais reproposée.
            continue;
        }

        VideoSuggestion::create(
            $artistId,
            $video['youtube_id'],
            $video['title'],
            $video['thumbnail_url'],
            $video['channel_name'],
            $video['published_at']
        );

        $found++;
    }
}

echo sprintf(
    "[%s] Scan terminé : %d chaîne(s) analysée(s), %d nouvelle(s) suggestion(s), %d erreur(s).\n",
    date('Y-m-d H:i:s'),
    $scanned,
    $found,
    $errors
);
