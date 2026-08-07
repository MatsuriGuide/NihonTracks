<?php

namespace App\Models;

use App\Core\Database;

class Playlist
{
    public static function findById(int $id): ?array
    {
        return Database::getInstance()->fetchOne(
            'SELECT * FROM playlists WHERE id = ?',
            [$id]
        );
    }

    public static function allPublic(): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT p.*, u.display_name AS owner_name,
                    (SELECT COUNT(*) FROM playlist_videos pv WHERE pv.playlist_id = p.id) AS video_count
             FROM playlists p
             JOIN users u ON u.id = p.user_id
             WHERE p.is_public = 1
             ORDER BY p.updated_at DESC'
        );
    }

    public static function allByUser(int $userId): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT p.*,
                    (SELECT COUNT(*) FROM playlist_videos pv WHERE pv.playlist_id = p.id) AS video_count
             FROM playlists p
             WHERE p.user_id = ?
             ORDER BY p.updated_at DESC',
            [$userId]
        );
    }

    public static function create(string $name, ?string $description, bool $isPublic, int $userId): int
    {
        $db = Database::getInstance();

        $db->query(
            'INSERT INTO playlists (user_id, name, description, is_public) VALUES (?, ?, ?, ?)',
            [$userId, $name, $description, $isPublic ? 1 : 0]
        );

        return (int) $db->lastInsertId();
    }

    public static function update(int $id, string $name, ?string $description, bool $isPublic): void
    {
        Database::getInstance()->query(
            'UPDATE playlists SET name = ?, description = ?, is_public = ? WHERE id = ?',
            [$name, $description, $isPublic ? 1 : 0, $id]
        );
    }

    public static function delete(int $id): void
    {
        Database::getInstance()->query('DELETE FROM playlists WHERE id = ?', [$id]);
    }

    public static function videosFor(int $playlistId): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT v.id, v.youtube_id, v.thumbnail_url, v.release_date,
                    vi.title,
                    GROUP_CONCAT(DISTINCT ai.name ORDER BY ai.name SEPARATOR ", ") AS artist_names,
                    pv.position, pv.added_at
             FROM playlist_videos pv
             JOIN videos v ON v.id = pv.video_id
             LEFT JOIN videos_i18n vi ON vi.video_id = v.id AND vi.lang = "fr"
             LEFT JOIN video_artists va ON va.video_id = v.id
             LEFT JOIN artists_i18n ai ON ai.artist_id = va.artist_id AND ai.lang = "fr"
             WHERE pv.playlist_id = ?
             GROUP BY v.id, pv.position, pv.added_at
             ORDER BY pv.position ASC, pv.added_at ASC',
            [$playlistId]
        );
    }

    public static function addVideo(int $playlistId, int $videoId): void
    {
        $db = Database::getInstance();

        $maxPosition = $db->fetchOne(
            'SELECT MAX(position) AS max_pos FROM playlist_videos WHERE playlist_id = ?',
            [$playlistId]
        )['max_pos'] ?? 0;

        $db->query(
            'INSERT IGNORE INTO playlist_videos (playlist_id, video_id, position) VALUES (?, ?, ?)',
            [$playlistId, $videoId, (int) $maxPosition + 1]
        );
    }

    public static function removeVideo(int $playlistId, int $videoId): void
    {
        Database::getInstance()->query(
            'DELETE FROM playlist_videos WHERE playlist_id = ? AND video_id = ?',
            [$playlistId, $videoId]
        );
    }
}
