<?php

namespace App\Models;

use App\Core\Database;

class Video
{
    public static function findByYoutubeId(string $youtubeId): ?array
    {
        return Database::getInstance()->fetchOne(
            'SELECT * FROM videos WHERE youtube_id = ?',
            [$youtubeId]
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::getInstance()->fetchOne(
            'SELECT * FROM videos WHERE id = ?',
            [$id]
        );
    }

    public static function all(?string $lang = null): array
    {
        $lang ??= \App\Core\Lang::current();

        return Database::getInstance()->fetchAll(
            'SELECT v.id, v.youtube_id, v.thumbnail_url, v.release_date, v.video_type,
                    COALESCE(vi.title, vi_fr.title) AS title,
                    GROUP_CONCAT(DISTINCT COALESCE(ai.name, ai_fr.name) ORDER BY ai.name SEPARATOR ", ") AS artist_names
             FROM videos v
             LEFT JOIN videos_i18n vi ON vi.video_id = v.id AND vi.lang = ?
             LEFT JOIN videos_i18n vi_fr ON vi_fr.video_id = v.id AND vi_fr.lang = "fr"
             LEFT JOIN video_artists va ON va.video_id = v.id
             LEFT JOIN artists_i18n ai ON ai.artist_id = va.artist_id AND ai.lang = ?
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = va.artist_id AND ai_fr.lang = "fr"
             WHERE v.status = "published"
             GROUP BY v.id
             ORDER BY v.release_date DESC, v.id DESC',
            [$lang, $lang]
        );
    }

    public static function artistsFor(int $videoId, ?string $lang = null): array
    {
        $lang ??= \App\Core\Lang::current();

        return Database::getInstance()->fetchAll(
            'SELECT a.id, a.slug, COALESCE(ai.name, ai_fr.name) AS name
             FROM video_artists va
             JOIN artists a ON a.id = va.artist_id
             LEFT JOIN artists_i18n ai ON ai.artist_id = a.id AND ai.lang = ?
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = a.id AND ai_fr.lang = "fr"
             WHERE va.video_id = ?',
            [$lang, $videoId]
        );
    }

    public static function tagsFor(int $videoId, ?string $lang = null): array
    {
        $lang ??= \App\Core\Lang::current();

        return Database::getInstance()->fetchAll(
            'SELECT t.id, COALESCE(ti.name, ti_fr.name) AS name, tc.slug AS category_slug
             FROM video_tags vt
             JOIN tags t ON t.id = vt.tag_id
             LEFT JOIN tags_i18n ti ON ti.tag_id = t.id AND ti.lang = ?
             LEFT JOIN tags_i18n ti_fr ON ti_fr.tag_id = t.id AND ti_fr.lang = "fr"
             LEFT JOIN tag_categories tc ON tc.id = t.category_id
             WHERE vt.video_id = ?',
            [$lang, $videoId]
        );
    }

    /**
     * Retourne les traductions existantes d'une vidéo, indexées par langue.
     */
    public static function translations(int $videoId): array
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT lang, title, description FROM videos_i18n WHERE video_id = ?',
            [$videoId]
        );

        $result = [];
        foreach ($rows as $row) {
            $result[$row['lang']] = $row;
        }

        return $result;
    }

    public static function translation(int $videoId, ?string $lang = null): ?array
    {
        $lang ??= \App\Core\Lang::current();

        $translation = Database::getInstance()->fetchOne(
            'SELECT * FROM videos_i18n WHERE video_id = ? AND lang = ?',
            [$videoId, $lang]
        );

        if ($translation !== null || $lang === 'fr') {
            return $translation;
        }

        return Database::getInstance()->fetchOne(
            'SELECT * FROM videos_i18n WHERE video_id = ? AND lang = "fr"',
            [$videoId]
        );
    }

    public static function create(array $data, array $artistIds, array $tagIds, int $addedBy): int
    {
        $db = Database::getInstance();

        $db->query(
            'INSERT INTO videos (youtube_id, youtube_url, release_date, video_type, thumbnail_url, channel_name, duration_seconds, added_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['youtube_id'],
                $data['youtube_url'],
                $data['release_date'],
                $data['video_type'],
                $data['thumbnail_url'],
                $data['channel_name'],
                $data['duration_seconds'],
                $addedBy,
            ]
        );

        $videoId = (int) $db->lastInsertId();

        $db->query(
            'INSERT INTO videos_i18n (video_id, lang, title) VALUES (?, "fr", ?)',
            [$videoId, $data['title']]
        );

        foreach ($artistIds as $artistId) {
            $db->query(
                'INSERT IGNORE INTO video_artists (video_id, artist_id, role) VALUES (?, ?, "main")',
                [$videoId, $artistId]
            );
        }

        foreach ($tagIds as $tagId) {
            $db->query(
                'INSERT IGNORE INTO video_tags (video_id, tag_id) VALUES (?, ?)',
                [$videoId, $tagId]
            );
        }

        return $videoId;
    }

    public static function update(int $id, array $data, array $artistIds, array $tagIds): void
    {
        $db = Database::getInstance();

        $db->query(
            'UPDATE videos SET release_date = ?, video_type = ? WHERE id = ?',
            [$data['release_date'], $data['video_type'], $id]
        );

        $existing = $db->fetchOne(
            'SELECT id FROM videos_i18n WHERE video_id = ? AND lang = "fr"',
            [$id]
        );

        if ($existing) {
            $db->query(
                'UPDATE videos_i18n SET title = ? WHERE video_id = ? AND lang = "fr"',
                [$data['title'], $id]
            );
        } else {
            $db->query(
                'INSERT INTO videos_i18n (video_id, lang, title) VALUES (?, "fr", ?)',
                [$id, $data['title']]
            );
        }

        $db->query('DELETE FROM video_artists WHERE video_id = ?', [$id]);
        foreach ($artistIds as $artistId) {
            $db->query(
                'INSERT IGNORE INTO video_artists (video_id, artist_id, role) VALUES (?, ?, "main")',
                [$id, $artistId]
            );
        }

        $db->query('DELETE FROM video_tags WHERE video_id = ?', [$id]);
        foreach ($tagIds as $tagId) {
            $db->query(
                'INSERT IGNORE INTO video_tags (video_id, tag_id) VALUES (?, ?)',
                [$id, $tagId]
            );
        }
    }

    public static function delete(int $id): void
    {
        Database::getInstance()->query('DELETE FROM videos WHERE id = ?', [$id]);
    }
}
