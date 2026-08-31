<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Lang;

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
        $lang ??= Lang::current();

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

    /**
     * Liste filtrable par artiste et/ou tags. Plusieurs tags sélectionnés
     * sont combinés en ET (la vidéo doit avoir TOUS les tags choisis, pas
     * seulement l'un d'eux) — utile pour croiser genre + langue par exemple.
     *
     * @param int[] $tagIds
     */
    public static function filtered(?int $artistId, array $tagIds, ?string $lang = null): array
    {
        $lang ??= Lang::current();
        $tagIds = array_values(array_unique(array_map('intval', $tagIds)));

        $sql = 'SELECT v.id, v.youtube_id, v.thumbnail_url, v.release_date, v.video_type,
                       COALESCE(vi.title, vi_fr.title) AS title,
                       GROUP_CONCAT(DISTINCT COALESCE(ai.name, ai_fr.name) ORDER BY ai.name SEPARATOR ", ") AS artist_names
                FROM videos v
                LEFT JOIN videos_i18n vi ON vi.video_id = v.id AND vi.lang = ?
                LEFT JOIN videos_i18n vi_fr ON vi_fr.video_id = v.id AND vi_fr.lang = "fr"
                LEFT JOIN video_artists va ON va.video_id = v.id
                LEFT JOIN artists_i18n ai ON ai.artist_id = va.artist_id AND ai.lang = ?
                LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = va.artist_id AND ai_fr.lang = "fr"
                WHERE v.status = "published"';
        $params = [$lang, $lang];

        if ($artistId !== null) {
            $sql .= ' AND EXISTS (
                SELECT 1 FROM video_artists va2
                WHERE va2.video_id = v.id AND va2.artist_id = ?
            )';
            $params[] = $artistId;
        }

        if (!empty($tagIds)) {
            $placeholders = implode(',', array_fill(0, count($tagIds), '?'));
            $sql .= " AND v.id IN (
                SELECT vt.video_id FROM video_tags vt
                WHERE vt.tag_id IN ({$placeholders})
                GROUP BY vt.video_id
                HAVING COUNT(DISTINCT vt.tag_id) = ?
            )";
            foreach ($tagIds as $tagId) {
                $params[] = $tagId;
            }
            $params[] = count($tagIds);
        }

        $sql .= ' GROUP BY v.id ORDER BY v.release_date DESC, v.id DESC';

        return Database::getInstance()->fetchAll($sql, $params);
    }

    /**
     * Vidéos d'un artiste donné, pour affichage en grille sur sa fiche.
     */
    public static function forArtist(int $artistId, ?string $lang = null): array
    {
        $lang ??= Lang::current();

        return Database::getInstance()->fetchAll(
            'SELECT v.id, v.youtube_id, v.thumbnail_url, v.release_date, v.video_type,
                    COALESCE(vi.title, vi_fr.title) AS title
             FROM videos v
             JOIN video_artists va ON va.video_id = v.id AND va.artist_id = ?
             LEFT JOIN videos_i18n vi ON vi.video_id = v.id AND vi.lang = ?
             LEFT JOIN videos_i18n vi_fr ON vi_fr.video_id = v.id AND vi_fr.lang = "fr"
             WHERE v.status = "published"
             ORDER BY v.release_date DESC, v.id DESC',
            [$artistId, $lang]
        );
    }

    public static function artistsFor(int $videoId, ?string $lang = null): array
    {
        $lang ??= Lang::current();

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
        $lang ??= Lang::current();

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
        $lang ??= Lang::current();

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

    public static function create(array $data, array $artistIds, array $tagIds, int $addedBy, string $source = 'manual'): int
    {
        $db = Database::getInstance();

        $db->query(
            'INSERT INTO videos (youtube_id, youtube_url, release_date, video_type, thumbnail_url, channel_name, duration_seconds, added_by, source)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['youtube_id'],
                $data['youtube_url'],
                $data['release_date'],
                $data['video_type'],
                $data['thumbnail_url'],
                $data['channel_name'],
                $data['duration_seconds'],
                $addedBy,
                $source,
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

        // Éditer une vidéo auto-publiée vaut relecture implicite — inutile
        // de la faire réapparaître dans la file "à relire" après coup.
        $db->query(
            'UPDATE videos SET release_date = ?, video_type = ?, reviewed_at = COALESCE(reviewed_at, NOW()) WHERE id = ?',
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

    public static function countPublished(): int
    {
        return (int) (Database::getInstance()->fetchOne(
            'SELECT COUNT(*) AS n FROM videos WHERE status = "published"'
        )['n'] ?? 0);
    }

    public static function delete(int $id): void
    {
        Database::getInstance()->query('DELETE FROM videos WHERE id = ?', [$id]);
    }

    /**
     * Vidéos publiées automatiquement par le scan de chaîne, pas encore
     * relues par un modérateur/admin (type vidéo par défaut, tags copiés
     * de l'artiste au moment de l'ajout — à vérifier/corriger après coup).
     */
    public static function allNeedingReview(?string $lang = null, int $limit = 24, int $offset = 0): array
    {
        $lang ??= Lang::current();
        $limit = max(1, min(100, $limit));
        $offset = max(0, $offset);

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
             WHERE v.source = "auto_scan" AND v.reviewed_at IS NULL
             GROUP BY v.id
             ORDER BY v.created_at DESC
             LIMIT ' . $limit . ' OFFSET ' . $offset,
            [$lang, $lang]
        );
    }

    public static function countNeedingReview(): int
    {
        return (int) (Database::getInstance()->fetchOne(
            'SELECT COUNT(*) AS n FROM videos WHERE source = "auto_scan" AND reviewed_at IS NULL'
        )['n'] ?? 0);
    }

    public static function updateType(int $id, string $videoType): void
    {
        Database::getInstance()->query(
            'UPDATE videos SET video_type = ? WHERE id = ?',
            [$videoType, $id]
        );
    }

    public static function markReviewed(int $id): void
    {
        Database::getInstance()->query(
            'UPDATE videos SET reviewed_at = NOW() WHERE id = ?',
            [$id]
        );
    }
}
