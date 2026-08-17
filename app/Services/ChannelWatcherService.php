<?php

namespace App\Services;

use App\Models\VideoSuggestion;

class ChannelWatcherService
{
    /**
     * Scanne la chaîne d'un artiste et enregistre les nouvelles suggestions.
     * Retourne le nombre de nouvelles vidéos détectées (0 en cas d'échec API).
     *
     * @param int $maxResults Nombre de vidéos récentes examinées par scan (max 50)
     * @param int $minDurationSeconds Filtre heuristique anti-Shorts : les vidéos
     *   plus courtes que ce seuil sont ignorées. Imparfait — YouTube autorise
     *   des Shorts jusqu'à 3 min depuis 2024, donc certains passeront quand
     *   même le filtre — mais un vrai clip musical dépasse presque toujours
     *   90 secondes, donc ça élimine l'essentiel des teasers/extraits courts.
     */
    public static function scanArtist(
        int $artistId,
        string $channelId,
        int $maxResults = 50,
        int $minDurationSeconds = 90
    ): int {
        $uploadsPlaylistId = YoutubeApiService::fetchUploadsPlaylistId($channelId);

        if ($uploadsPlaylistId === null) {
            return 0;
        }

        $videos = YoutubeApiService::fetchPlaylistVideos($uploadsPlaylistId, $maxResults);

        if (empty($videos)) {
            return 0;
        }

        $durations = YoutubeApiService::fetchVideosDurations(array_column($videos, 'youtube_id'));

        $found = 0;

        foreach ($videos as $video) {
            $duration = $durations[$video['youtube_id']] ?? null;

            if ($duration !== null && $duration < $minDurationSeconds) {
                continue;
            }

            if (VideoSuggestion::youtubeIdKnown($video['youtube_id'])) {
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

        return $found;
    }
}
