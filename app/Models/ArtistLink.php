<?php

namespace App\Models;

use App\Core\Database;

class ArtistLink
{
    public const PLATFORMS = ['website', 'twitter', 'instagram', 'facebook', 'tiktok', 'youtube', 'spotify', 'other'];

    public static function forArtist(int $artistId): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT * FROM artist_links WHERE artist_id = ? ORDER BY FIELD(platform, "youtube", "website", "twitter", "instagram", "facebook", "tiktok", "spotify", "other")',
            [$artistId]
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::getInstance()->fetchOne('SELECT * FROM artist_links WHERE id = ?', [$id]);
    }

    public static function create(int $artistId, string $platform, string $url): int
    {
        $db = Database::getInstance();

        $db->query(
            'INSERT INTO artist_links (artist_id, platform, url) VALUES (?, ?, ?)',
            [$artistId, $platform, $url]
        );

        return (int) $db->lastInsertId();
    }

    public static function delete(int $id): void
    {
        Database::getInstance()->query('DELETE FROM artist_links WHERE id = ?', [$id]);
    }

    /**
     * Extrait toutes les URLs d'un bloc de texte collé, détecte la
     * plateforme de chacune selon son domaine, et les enregistre — sans
     * dupliquer les liens déjà présents pour cet artiste.
     *
     * @return int Nombre de liens réellement ajoutés
     */
    public static function addBulk(int $artistId, string $rawText): int
    {
        preg_match_all('#https?://[^\s,;"\'<>]+#i', $rawText, $matches);
        $urls = array_unique($matches[0] ?? []);

        if (empty($urls)) {
            return 0;
        }

        $existing = array_map(
            static fn (array $link): string => $link['url'],
            self::forArtist($artistId)
        );

        $added = 0;

        foreach ($urls as $url) {
            // Nettoyage de la ponctuation de fin fréquente lors d'un copier-coller
            $url = rtrim($url, '.,;)');

            if (in_array($url, $existing, true)) {
                continue;
            }

            self::create($artistId, self::detectPlatform($url), $url);
            $existing[] = $url;
            $added++;
        }

        return $added;
    }

    /**
     * Devine la plateforme d'une URL à partir de son domaine.
     * Repli sur "website" si aucun domaine connu ne correspond.
     */
    public static function detectPlatform(string $url): string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        $map = [
            'twitter.com'      => 'twitter',
            'x.com'            => 'twitter',
            'instagram.com'    => 'instagram',
            'facebook.com'     => 'facebook',
            'fb.com'           => 'facebook',
            'tiktok.com'       => 'tiktok',
            'youtube.com'      => 'youtube',
            'youtu.be'         => 'youtube',
            'open.spotify.com' => 'spotify',
            'spotify.com'      => 'spotify',
        ];

        foreach ($map as $domain => $platform) {
            if ($host === $domain || substr($host, -(strlen($domain) + 1)) === '.' . $domain) {
                return $platform;
            }
        }

        return 'website';
    }
}
