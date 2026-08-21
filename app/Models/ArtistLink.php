<?php

namespace App\Models;

use App\Core\Database;
use App\Services\YoutubeApiService;

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
            $url = rtrim($url, '.,;)');

            $platform = self::detectPlatform($url);

            // Un lien YouTube en /@handle n'est pas exploitable par la
            // surveillance de chaîne (qui exige /channel/UC...) : on tente
            // de le résoudre vers son URL canonique avant stockage. En cas
            // d'échec (clé API absente, quota, handle invalide...), on garde
            // l'URL telle quelle plutôt que de perdre le lien.
            if ($platform === 'youtube' && strpos($url, '/channel/') === false) {
                $channelInfo = YoutubeApiService::fetchChannelInfo($url);

                if ($channelInfo !== null) {
                    $url = $channelInfo['canonical_url'];
                }
            }

            if (in_array($url, $existing, true)) {
                continue;
            }

            self::create($artistId, $platform, $url);
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

    /**
     * Retrouve le ou les artistes APPROUVÉS dont le lien YouTube pointe vers
     * cet ID de chaîne (URL au format /channel/UC...). Les artistes en
     * attente de validation ne sont jamais proposés à la détection auto.
     */
    public static function findArtistIdsByYoutubeChannelId(string $channelId): array
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT DISTINCT al.artist_id
             FROM artist_links al
             JOIN artists a ON a.id = al.artist_id AND a.moderation_status = "approved"
             WHERE al.platform = "youtube" AND al.url LIKE ?',
            ['%' . $channelId . '%']
        );

        return array_map(static fn (array $r): int => (int) $r['artist_id'], $rows);
    }

    /**
     * Un artiste (approuvé ou non) a-t-il déjà cette chaîne YouTube
     * enregistrée ? Utilisé par l'ajout rapide pour éviter les doublons.
     */
    public static function findArtistByYoutubeChannelId(string $channelId): ?array
    {
        return Database::getInstance()->fetchOne(
            'SELECT a.id, a.slug, COALESCE(ai.name, ai_fr.name) AS name, a.moderation_status
             FROM artist_links al
             JOIN artists a ON a.id = al.artist_id
             LEFT JOIN artists_i18n ai ON ai.artist_id = a.id AND ai.lang = "fr"
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = a.id AND ai_fr.lang = "fr"
             WHERE al.platform = "youtube" AND al.url LIKE ?
             LIMIT 1',
            ['%' . $channelId . '%']
        );
    }
}
