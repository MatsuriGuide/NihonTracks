<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\Video;

class ChannelWatcherService
{
    /**
     * Scanne la chaîne d'un artiste et publie directement les nouvelles
     * vidéos trouvées (plus de file d'attente à valider). Chaque vidéo
     * hérite des tags actuels de l'artiste au moment de sa création — une
     * "photo" indépendante ensuite, pas un lien permanent. Le type de vidéo
     * par défaut ("other" — volontairement neutre, à corriger via l'écran
     * de relecture) et les tags restent à corriger si besoin via
     * l'écran de relecture /admin/video-review.
     *
     * Retourne le nombre de nouvelles vidéos publiées (null en cas d'échec API).
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
        if (!empty($channelDetails['thumbnail_url'])) {
            Artist::updateAvatar($artistId, $channelDetails['thumbnail_url']);
        }
        Artist::updateSubscriberCount($artistId, $channelDetails['subscriber_count']);

        $videos = YoutubeApiService::fetchPlaylistVideos($channelDetails['uploads_playlist_id'], $maxResults);

        if (empty($videos)) {
            return 0;
        }

        $artist = Artist::findById($artistId);

        if ($artist === null) {
            return null;
        }

        $tagIds = Artist::tagIdsFor($artistId);
        $addedBy = (int) $artist['created_by'];

        $durations = YoutubeApiService::fetchVideosDurations(array_column($videos, 'youtube_id'));

        $published = 0;

        foreach ($videos as $video) {
            $duration = $durations[$video['youtube_id']] ?? null;

            if ($duration !== null && $duration < $minDurationSeconds) {
                continue;
            }

            if (Video::findByYoutubeId($video['youtube_id']) !== null) {
                continue;
            }

            Video::create(
                [
                    'youtube_id'       => $video['youtube_id'],
                    'youtube_url'      => 'https://www.youtube.com/watch?v=' . $video['youtube_id'],
                    'title'            => $video['title'],
                    'release_date'     => $video['published_at'],
                    'video_type'       => 'other',
                    'thumbnail_url'    => $video['thumbnail_url'],
                    'channel_name'     => $video['channel_name'],
                    'duration_seconds' => $duration,
                ],
                [$artistId],
                $tagIds,
                $addedBy,
                'auto_scan'
            );

            $published++;
        }

        return $published;
    }
}
