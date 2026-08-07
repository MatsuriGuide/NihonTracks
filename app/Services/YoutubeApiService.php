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
        try {
            $interval = new \DateInterval($iso8601);
        } catch (\Exception) {
            return 0;
        }

        return ($interval->d * 86400) + ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
    }
}
