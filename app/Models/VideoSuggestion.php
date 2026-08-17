<?php

namespace App\Models;

use App\Core\Database;

class VideoSuggestion
{
    public static function youtubeIdKnown(string $youtubeId): bool
    {
        $db = Database::getInstance();

        if ($db->fetchOne('SELECT id FROM video_suggestions WHERE youtube_id = ?', [$youtubeId]) !== null) {
            return true;
        }

        return $db->fetchOne('SELECT id FROM videos WHERE youtube_id = ?', [$youtubeId]) !== null;
    }

    public static function create(
        int $artistId,
        string $youtubeId,
        string $title,
        ?string $thumbnailUrl,
        ?string $channelName,
        ?string $publishedAt
    ): int {
        $db = Database::getInstance();

        $db->query(
            'INSERT INTO video_suggestions (artist_id, youtube_id, title, thumbnail_url, channel_name, published_at)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$artistId, $youtubeId, $title, $thumbnailUrl, $channelName, $publishedAt]
        );

        return (int) $db->lastInsertId();
    }

    public static function findById(int $id): ?array
    {
        return Database::getInstance()->fetchOne('SELECT * FROM video_suggestions WHERE id = ?', [$id]);
    }

    public static function allPending(?string $lang = null): array
    {
        $lang ??= \App\Core\Lang::current();

        return Database::getInstance()->fetchAll(
            'SELECT vs.*, COALESCE(ai.name, ai_fr.name) AS artist_name, a.slug AS artist_slug
             FROM video_suggestions vs
             JOIN artists a ON a.id = vs.artist_id
             LEFT JOIN artists_i18n ai ON ai.artist_id = a.id AND ai.lang = ?
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = a.id AND ai_fr.lang = "fr"
             WHERE vs.status = "pending"
             ORDER BY vs.discovered_at DESC',
            [$lang]
        );
    }

    public static function countPending(): int
    {
        return (int) (Database::getInstance()->fetchOne(
            'SELECT COUNT(*) AS n FROM video_suggestions WHERE status = "pending"'
        )['n'] ?? 0);
    }

    public static function dismiss(int $id, int $reviewedBy): void
    {
        Database::getInstance()->query(
            'UPDATE video_suggestions SET status = "dismissed", reviewed_by = ?, reviewed_at = NOW() WHERE id = ?',
            [$reviewedBy, $id]
        );
    }

    public static function markImported(int $id, int $reviewedBy): void
    {
        Database::getInstance()->query(
            'UPDATE video_suggestions SET status = "imported", reviewed_by = ?, reviewed_at = NOW() WHERE id = ?',
            [$reviewedBy, $id]
        );
    }
}
