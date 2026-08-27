<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\VideoSuggestion;

class ChannelWatcherService
{
    /**
     * Scanne la chaîne d'un artiste et enregistre les nouvelles suggestions.
     * Retourne le nombre de nouvelles vidéos détectées (null en cas d'échec API).
     *
     * @param int $maxResults Nombre de vidéos récentes examinées par scan (max 50 par page)
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
    ): ?int {
        $channelDetails = YoutubeApiService::fetchChannelDetailsById($channelId);

        if ($channelDetails === null || empty($channelDetails['uploads_playlist_id'])) {
            return null;
        }

        // Effet de bord "gratuit" : la photo et le nombre d'abonnés viennent
        // du même appel API que celui qui sert à trouver les nouvelles
        // vidéos, donc les tenir à jour à chaque scan ne coûte rien de plus.
        // La photo n'est écrasée que si l'API en renvoie bien une (on ne
        // veut pas effacer une photo existante à cause d'un aléa temporaire).
        if (!empty($channelDetails['thumbnail_url'])) {
            Artist::updateAvatar($artistId, $channelDetails['thumbnail_url']);
        }
        Artist::updateSubscriberCount($artistId, $channelDetails['subscriber_count']);

        $videos = YoutubeApiService::fetchPlaylistVideos($channelDetails['uploads_playlist_id'], $maxResults);

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
