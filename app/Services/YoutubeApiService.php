<?php

namespace App\Services;

class YoutubeApiService
{
    /**
     * Extrait l'ID vidéo (11 caractères) de n'importe quel format d'URL YouTube :
     * watch?v=, youtu.be/, embed/, shorts/.
     */
    public static function extractVideoId(string $url): ?string
    {
        $url = trim($url);

        if (preg_match('/(?:v=|\/shorts\/|\/embed\/|youtu\.be\/)([0-9A-Za-z_-]{11})/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Récupère les métadonnées d'une vidéo via YouTube Data API v3.
     * Retourne null en cas d'échec (clé absente/invalide, quota atteint, vidéo introuvable)
     * — l'appelant doit alors basculer sur la saisie manuelle.
     *
     * @return array{
     *   youtube_id: string,
     *   title: string,
     *   channel_name: ?string,
     *   channel_id: ?string,
     *   release_date: ?string,
     *   thumbnail_url: ?string,
     *   duration_seconds: ?int
     * }|null
     */
    public static function fetchMetadata(string $videoId): ?array
    {
        $apiKey = $_ENV['YOUTUBE_API_KEY'] ?? '';

        if ($apiKey === '') {
            return null;
        }

        $endpoint = 'https://www.googleapis.com/youtube/v3/videos'
            . '?part=snippet,contentDetails'
            . '&id=' . urlencode($videoId)
            . '&key=' . urlencode($apiKey);

        $response = self::httpGet($endpoint);

        if ($response === null) {
            return null;
        }

        $data = json_decode($response, true);

        if (empty($data['items'][0])) {
            return null;
        }

        $item = $data['items'][0];
        $snippet = $item['snippet'] ?? [];
        $contentDetails = $item['contentDetails'] ?? [];

        return [
            'youtube_id'       => $videoId,
            'title'            => $snippet['title'] ?? '',
            'channel_name'     => $snippet['channelTitle'] ?? null,
            'channel_id'       => $snippet['channelId'] ?? null,
            'release_date'     => isset($snippet['publishedAt'])
                ? substr($snippet['publishedAt'], 0, 10)
                : null,
            'thumbnail_url'    => $snippet['thumbnails']['high']['url']
                ?? $snippet['thumbnails']['default']['url']
                ?? null,
            'duration_seconds' => isset($contentDetails['duration'])
                ? self::parseDuration($contentDetails['duration'])
                : null,
        ];
    }

    /**
     * Extrait l'ID de chaîne (UC...) d'une URL au format /channel/UC...
     * Ne fonctionne que sur ce format précis (pas /@handle ni /c/Nom).
     */
    public static function extractChannelId(string $url): ?string
    {
        if (preg_match('#/channel/([A-Za-z0-9_-]{10,})#', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Récupère titre + ID canonique d'une chaîne à partir de n'importe quelle
     * URL usuelle : /channel/UC..., ou /@handle. Pour /c/NomPersonnalisé et
     * /user/AncienPseudo (non fiables sans appel supplémentaire), retourne null.
     *
     * @return array{channel_id: string, title: string, canonical_url: string}|null
     */
    public static function fetchChannelInfo(string $url): ?array
    {
        $apiKey = $_ENV['YOUTUBE_API_KEY'] ?? '';

        if ($apiKey === '') {
            return null;
        }

        $channelId = self::extractChannelId($url);
        $queryParam = null;

        if ($channelId !== null) {
            $queryParam = 'id=' . urlencode($channelId);
        } elseif (preg_match('#youtube\.com/@([A-Za-z0-9_.-]+)#i', $url, $matches)) {
            $queryParam = 'forHandle=' . urlencode('@' . $matches[1]);
        } else {
            return null;
        }

        $endpoint = 'https://www.googleapis.com/youtube/v3/channels'
            . '?part=snippet'
            . '&' . $queryParam
            . '&key=' . urlencode($apiKey);

        $response = self::httpGet($endpoint);

        if ($response === null) {
            return null;
        }

        $data = json_decode($response, true);

        if (empty($data['items'][0])) {
            return null;
        }

        $item = $data['items'][0];
        $resolvedId = $item['id'] ?? $channelId;

        if ($resolvedId === null) {
            return null;
        }

        return [
            'channel_id'    => $resolvedId,
            'title'         => $item['snippet']['title'] ?? '',
            // On stocke toujours l'URL canonique /channel/UC..., quel que soit
            // le format saisi au départ — c'est le seul format exploitable
            // par la détection auto et la surveillance de chaîne.
            'canonical_url' => 'https://www.youtube.com/channel/' . $resolvedId,
        ];
    }

    /**
     * Récupère l'ID de la playlist "uploads" d'une chaîne (toutes ses vidéos,
     * dans l'ordre de publication). 1 unité de quota.
     */
    public static function fetchUploadsPlaylistId(string $channelId): ?string
    {
        $apiKey = $_ENV['YOUTUBE_API_KEY'] ?? '';

        if ($apiKey === '') {
            return null;
        }

        $endpoint = 'https://www.googleapis.com/youtube/v3/channels'
            . '?part=contentDetails'
            . '&id=' . urlencode($channelId)
            . '&key=' . urlencode($apiKey);

        $response = self::httpGet($endpoint);

        if ($response === null) {
            return null;
        }

        $data = json_decode($response, true);

        return $data['items'][0]['contentDetails']['relatedPlaylists']['uploads'] ?? null;
    }

    /**
     * Récupère les $maxResults vidéos les plus récentes d'une playlist
     * "uploads". Coût : 1 unité de quota par tranche de 50 résultats
     * (donc 1 unité jusqu'à 50, 2 unités pour 51-100, etc.).
     *
     * @return array<int, array{youtube_id: string, title: string, thumbnail_url: ?string, channel_name: ?string, published_at: ?string}>
     */
    public static function fetchPlaylistVideos(string $playlistId, int $maxResults = 50): array
    {
        $apiKey = $_ENV['YOUTUBE_API_KEY'] ?? '';

        if ($apiKey === '') {
            return [];
        }

        $videos = [];
        $pageToken = null;

        do {
            $remaining = $maxResults - count($videos);
            $pageSize = max(1, min(50, $remaining));

            $endpoint = 'https://www.googleapis.com/youtube/v3/playlistItems'
                . '?part=snippet'
                . '&playlistId=' . urlencode($playlistId)
                . '&maxResults=' . $pageSize
                . '&key=' . urlencode($apiKey)
                . ($pageToken !== null ? '&pageToken=' . urlencode($pageToken) : '');

            $response = self::httpGet($endpoint);

            if ($response === null) {
                break;
            }

            $data = json_decode($response, true);

            foreach ($data['items'] ?? [] as $item) {
                $snippet = $item['snippet'] ?? [];
                $videoId = $snippet['resourceId']['videoId'] ?? null;

                if ($videoId === null) {
                    continue;
                }

                $videos[] = [
                    'youtube_id'    => $videoId,
                    'title'         => $snippet['title'] ?? '',
                    'thumbnail_url' => $snippet['thumbnails']['high']['url']
                        ?? $snippet['thumbnails']['default']['url']
                        ?? null,
                    'channel_name'  => $snippet['videoOwnerChannelTitle'] ?? $snippet['channelTitle'] ?? null,
                    'published_at'  => isset($snippet['publishedAt']) ? substr($snippet['publishedAt'], 0, 10) : null,
                ];
            }

            $pageToken = $data['nextPageToken'] ?? null;
        } while ($pageToken !== null && count($videos) < $maxResults);

        return array_slice($videos, 0, $maxResults);
    }

    /**
     * Récupère la durée (en secondes) de plusieurs vidéos. Coût : 1 unité de
     * quota par tranche de 50 IDs.
     *
     * @param string[] $videoIds
     * @return array<string, int> Durée indexée par ID vidéo
     */
    public static function fetchVideosDurations(array $videoIds): array
    {
        $apiKey = $_ENV['YOUTUBE_API_KEY'] ?? '';

        if ($apiKey === '' || empty($videoIds)) {
            return [];
        }

        $durations = [];

        foreach (array_chunk($videoIds, 50) as $chunk) {
            $endpoint = 'https://www.googleapis.com/youtube/v3/videos'
                . '?part=contentDetails'
                . '&id=' . urlencode(implode(',', $chunk))
                . '&key=' . urlencode($apiKey);

            $response = self::httpGet($endpoint);

            if ($response === null) {
                continue;
            }

            $data = json_decode($response, true);

            foreach ($data['items'] ?? [] as $item) {
                $id = $item['id'] ?? null;
                $duration = $item['contentDetails']['duration'] ?? null;

                if ($id !== null && $duration !== null) {
                    $durations[$id] = self::parseDuration($duration);
                }
            }
        }

        return $durations;
    }

    private static function httpGet(string $url): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $result = curl_exec($ch);
            $hasError = curl_errno($ch) !== 0;
            curl_close($ch);

            return (!$hasError && $result !== false) ? (string) $result : null;
        }

        $context = stream_context_create(['http' => ['timeout' => 8]]);
        $result = @file_get_contents($url, false, $context);

        return $result !== false ? $result : null;
    }

    private static function parseDuration(string $iso8601): int
    {
        // Compatible PHP 7.4+ : "catch (\Exception)" sans variable est une
        // syntaxe PHP 8.0+ ("catch non capturant"). On garde $e même inutilisé.
        try {
            $interval = new \DateInterval($iso8601);
        } catch (\Exception $e) {
            return 0;
        }

        return ($interval->d * 86400) + ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
    }
}
