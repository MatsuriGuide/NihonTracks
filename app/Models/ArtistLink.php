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
     * Retrouve le ou les artistes dont le lien YouTube pointe vers cet ID de
     * chaîne (URL au format /channel/UC...). Ne fonctionne que si le lien a
     * été renseigné sous ce format précis.
     */
    public static function findArtistIdsByYoutubeChannelId(string $channelId): array
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT DISTINCT artist_id FROM artist_links WHERE platform = "youtube" AND url LIKE ?',
            ['%' . $channelId . '%']
        );

        return array_map(static fn (array $r): int => (int) $r['artist_id'], $rows);
    }
}
