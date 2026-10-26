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
     * @param bool $refreshChannel Appelle channels.list pour mettre à jour avatar et abonnés (true par défaut, false pour économiser du quota en CRON)
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
        int $minDurationSeconds = 90,
        bool $refreshChannel = true
    ): ?int {
        $uploadsPlaylistId = null;

        // channels.list (1 unité) ne sert qu'à rafraîchir avatar + nombre
        // d'abonnés, et à trouver la playlist "uploads". Or celle-ci se déduit
        // de l'ID de chaîne (UC... -> UU...) : on peut donc s'en passer lors
        // des scans automatiques, sauf pour le rafraîchissement périodique.
        if ($refreshChannel || strncmp($channelId, 'UC', 2) !== 0) {
            $channelDetails = YoutubeApiService::fetchChannelDetailsById($channelId);

            if ($channelDetails === null || empty($channelDetails['uploads_playlist_id'])) {
                return null;
            }

            if (!empty($channelDetails['thumbnail_url'])) {
                Artist::updateAvatar($artistId, $channelDetails['thumbnail_url']);
            }
            Artist::updateSubscriberCount($artistId, $channelDetails['subscriber_count']);

            $uploadsPlaylistId = $channelDetails['uploads_playlist_id'];
        } else {
            $uploadsPlaylistId = 'UU' . substr($channelId, 2);
        }

        $videos = YoutubeApiService::fetchPlaylistVideos($uploadsPlaylistId, $maxResults);

        if (empty($videos)) {
            return 0;
        }

        // On écarte d'abord ce qui est déjà en base : si rien de nouveau, on
        // évite l'appel videos.list (1 unité) pour cette chaîne.
        $videos = array_values(array_filter($videos, static function (array $v): bool {
            return Video::findByYoutubeId($v['youtube_id']) === null;
        }));

        if (empty($videos)) {
            return 0;
        }

        $artist = Artist::findById($artistId);

        if ($artist === null) {
            return null;
        }

        $tagIds = Artist::tagIdsFor($artistId);
        $addedBy = (int) $artist['created_by'];

        // Un seul appel groupé pour la durée ET le statut de diffusion
        // (voir YoutubeApiService::fetchVideosDetails) — "snippet" ajouté
        // à côté de "contentDetails" ne coûte rien de plus en quota.
        $details = YoutubeApiService::fetchVideosDetails(array_column($videos, 'youtube_id'));

        $published = 0;

        foreach ($videos as $video) {
            $info = $details[$video['youtube_id']] ?? null;
            $duration = $info['duration_seconds'] ?? null;
            $liveStatus = $info['live_broadcast_content'] ?? 'none';

            // Une "première" programmée ("upcoming") ou en cours de
            // diffusion ("live") existe déjà sur la chaîne mais n'est pas
            // encore réellement regardable — on ne la publie pas tant
            // qu'elle n'est pas passée à "none". Elle sera reprise
            // automatiquement lors d'un prochain scan, une fois diffusée.
            if ($liveStatus !== 'none') {
                continue;
            }

            if ($duration !== null && $duration < $minDurationSeconds) {
                continue;
            }

            if (Video::findByYoutubeId($video['youtube_id']) !== null) {
                continue;
            }

            // Le titre de la liste de lecture ("uploads") est parfois un
            // texte de remplacement ("Deleted video", "Private video") même
            // pour une vidéo lisible : on préfère le titre de videos.list,
            // et on ignore la vidéo (reprise au prochain scan) si aucun
            // vrai titre n'est disponible.
            $title = $info['title'] ?? $video['title'];

            if (in_array(trim((string) $title), ['', 'Deleted video', 'Private video'], true)) {
                continue;
            }

            Video::create(
                [
                    'youtube_id'       => $video['youtube_id'],
                    'youtube_url'      => 'https://www.youtube.com/watch?v=' . $video['youtube_id'],
                    'title'            => $title,
                    'release_date'     => $video['published_at'],
                    'video_type'       => 'other',
                    'thumbnail_url'    => $video['thumbnail_url'],
                    'channel_name'     => $video['channel_name'],
                    'duration_seconds' => $duration,
                    // Vues de départ : base du premier calcul du TOP hebdomadaire.
                    'view_count'       => $info['view_count'] ?? null,
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
