<?php

namespace App\Services;

use App\Models\VideoSuggestion;

class ChannelWatcherService
{
    /**
     * Scanne la chaîne d'un artiste et enregistre les nouvelles suggestions.
     * Retourne le nombre de nouvelles vidéos détectées (0 en cas d'échec API).
     */
    public static function scanArtist(int $artistId, string $channelId, int $maxResults = 10): int
    {
        $uploadsPlaylistId = YoutubeApiService::fetchUploadsPlaylistId($channelId);

        if ($uploadsPlaylistId === null) {
            return 0;
        }

        $videos = YoutubeApiService::fetchPlaylistVideos($uploadsPlaylistId, $maxResults);
        $found = 0;

        foreach ($videos as $video) {
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
