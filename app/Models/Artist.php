<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Lang;

class Artist
{
    /**
     * Liste publique : uniquement les artistes approuvés.
     */
    public static function all(?string $lang = null): array
    {
        $lang ??= Lang::current();

        return Database::getInstance()->fetchAll(
            'SELECT a.id, a.slug, a.type, a.status, a.avatar_path,
                    COALESCE(ai.name, ai_fr.name) AS name
             FROM artists a
             LEFT JOIN artists_i18n ai ON ai.artist_id = a.id AND ai.lang = ?
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = a.id AND ai_fr.lang = "fr"
             WHERE a.moderation_status = "approved"
             ORDER BY name',
            [$lang]
        );
    }

    /**
     * File d'attente de modération : artistes soumis par un utilisateur
     * normal, en attente d'approbation par un modérateur/admin.
     */
    public static function allPending(?string $lang = null): array
    {
        $lang ??= Lang::current();

        return Database::getInstance()->fetchAll(
            'SELECT a.id, a.slug, a.type,
                    COALESCE(ai.name, ai_fr.name) AS name,
                    u.display_name AS submitted_by_name,
                    a.created_at
             FROM artists a
             LEFT JOIN artists_i18n ai ON ai.artist_id = a.id AND ai.lang = ?
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = a.id AND ai_fr.lang = "fr"
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.moderation_status = "pending"
             ORDER BY a.created_at ASC',
            [$lang]
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::getInstance()->fetchOne('SELECT * FROM artists WHERE id = ?', [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::getInstance()->fetchOne('SELECT * FROM artists WHERE slug = ?', [$slug]);
    }

    public static function translations(int $artistId): array
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT lang, name, bio FROM artists_i18n WHERE artist_id = ?',
            [$artistId]
        );

        $result = [];
        foreach ($rows as $row) {
            $result[$row['lang']] = $row;
        }

        return $result;
    }

    /**
     * Repli de détection : artistes approuvés dont le nom (langue courante
     * ou français) correspond exactement au nom de la chaîne YouTube.
     */
    public static function findIdsByExactName(string $name): array
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT DISTINCT ai.artist_id
             FROM artists_i18n ai
             JOIN artists a ON a.id = ai.artist_id AND a.moderation_status = "approved"
             WHERE LOWER(ai.name) = LOWER(?)',
            [$name]
        );

        return array_map(static fn (array $r): int => (int) $r['artist_id'], $rows);
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $db = Database::getInstance();

        if ($excludeId !== null) {
            $row = $db->fetchOne(
                'SELECT id FROM artists WHERE slug = ? AND id != ?',
                [$slug, $excludeId]
            );
        } else {
            $row = $db->fetchOne('SELECT id FROM artists WHERE slug = ?', [$slug]);
        }

        return $row !== null;
    }

    public static function create(array $data, string $name, ?string $bio, int $createdBy, string $moderationStatus = 'approved'): int
    {
        $db = Database::getInstance();

        $db->query(
            'INSERT INTO artists (type, status, moderation_status, start_year, end_year, label, slug, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['type'],
                $data['status'],
                $moderationStatus,
                $data['start_year'],
                $data['end_year'],
                $data['label'],
                $data['slug'],
                $createdBy,
            ]
        );

        $artistId = (int) $db->lastInsertId();

        $db->query(
            'INSERT INTO artists_i18n (artist_id, lang, name, bio) VALUES (?, "fr", ?, ?)',
            [$artistId, $name, $bio]
        );

        return $artistId;
    }

    public static function update(int $id, array $data, string $name, ?string $bio): void
    {
        $db = Database::getInstance();

        $db->query(
            'UPDATE artists SET type = ?, status = ?, start_year = ?, end_year = ?, label = ?, slug = ?
             WHERE id = ?',
            [
                $data['type'],
                $data['status'],
                $data['start_year'],
                $data['end_year'],
                $data['label'],
                $data['slug'],
                $id,
            ]
        );

        $existing = $db->fetchOne(
            'SELECT id FROM artists_i18n WHERE artist_id = ? AND lang = "fr"',
            [$id]
        );

        if ($existing) {
            $db->query(
                'UPDATE artists_i18n SET name = ?, bio = ? WHERE artist_id = ? AND lang = "fr"',
                [$name, $bio, $id]
            );
        } else {
            $db->query(
                'INSERT INTO artists_i18n (artist_id, lang, name, bio) VALUES (?, "fr", ?, ?)',
                [$id, $name, $bio]
            );
        }
    }

    /**
     * IDs des vidéos pour lesquelles cet artiste est le SEUL artiste
     * associé — les supprimer laisserait ces vidéos sans aucun artiste.
     * Utilisé pour bloquer la suppression d'un artiste tant que ces
     * vidéos n'ont pas été traitées (masquées, ou un autre artiste
     * ajouté dessus).
     *
     * @return int[]
     */
    public static function videoIdsWhereOnlyArtist(int $artistId): array
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT va.video_id
             FROM video_artists va
             WHERE va.artist_id = ?
               AND (SELECT COUNT(*) FROM video_artists va2 WHERE va2.video_id = va.video_id) = 1',
            [$artistId]
        );

        return array_map(static fn (array $r): int => (int) $r['video_id'], $rows);
    }

    /**
     * Supprime un artiste. Les vidéos pour lesquelles il était le SEUL
     * artiste sont masquées (pas supprimées en dur — cohérent avec
     * Video::hide(), pour qu'elles ne reviennent pas au prochain scan de
     * chaîne si elles existent toujours sur YouTube). Toutes les tables
     * qui référencent un artiste sont nettoyées explicitement, dans
     * l'ordre, avant de supprimer la ligne elle-même — plutôt que de
     * compter sur un éventuel ON DELETE CASCADE en base (dont la
     * configuration réelle, table par table, n'est pas garantie).
     *
     * @return int Nombre de vidéos masquées suite à cette suppression
     */
    public static function delete(int $id): int
    {
        $db = Database::getInstance();

        $orphanVideoIds = self::videoIdsWhereOnlyArtist($id);

        foreach ($orphanVideoIds as $videoId) {
            Video::hide($videoId);
        }

        $db->query('DELETE FROM video_artists WHERE artist_id = ?', [$id]);
        // Un artiste peut être des deux côtés d'une relation (membre de,
        // collabore avec...), d'où les deux colonnes à nettoyer.
        $db->query('DELETE FROM artist_relations WHERE artist_id = ? OR related_artist_id = ?', [$id, $id]);
        $db->query('DELETE FROM artist_tags WHERE artist_id = ?', [$id]);
        $db->query('DELETE FROM artist_links WHERE artist_id = ?', [$id]);
        $db->query('DELETE FROM artists_i18n WHERE artist_id = ?', [$id]);
        // Ancien système de suggestions (plus alimenté, mais d'anciennes
        // lignes peuvent encore exister et référencer cet artiste).
        $db->query('DELETE FROM video_suggestions WHERE artist_id = ?', [$id]);
        $db->query('DELETE FROM artists WHERE id = ?', [$id]);

        return count($orphanVideoIds);
    }

    public static function approve(int $id): void
    {
        Database::getInstance()->query(
            'UPDATE artists SET moderation_status = "approved" WHERE id = ?',
            [$id]
        );
    }

    public static function reject(int $id): void
    {
        Database::getInstance()->query(
            'UPDATE artists SET moderation_status = "rejected" WHERE id = ?',
            [$id]
        );
    }

    public static function countPending(): int
    {
        return (int) (Database::getInstance()->fetchOne(
            'SELECT COUNT(*) AS n FROM artists WHERE moderation_status = "pending"'
        )['n'] ?? 0);
    }

    public static function countApproved(): int
    {
        return (int) (Database::getInstance()->fetchOne(
            'SELECT COUNT(*) AS n FROM artists WHERE moderation_status = "approved"'
        )['n'] ?? 0);
    }

    public static function allIncomplete(?string $lang = null): array
    {
        $lang ??= Lang::current();

        return Database::getInstance()->fetchAll(
            'SELECT a.id, a.slug, a.start_year,
                    COALESCE(ai.name, ai_fr.name) AS name,
                    ai_fr.bio AS bio_fr,
                    (SELECT COUNT(*) FROM artist_tags at2 WHERE at2.artist_id = a.id) AS tag_count
             FROM artists a
             LEFT JOIN artists_i18n ai ON ai.artist_id = a.id AND ai.lang = ?
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = a.id AND ai_fr.lang = "fr"
             WHERE a.moderation_status = "approved"
               AND (
                   ai_fr.bio IS NULL OR ai_fr.bio = "" OR a.start_year IS NULL
                   OR NOT EXISTS (SELECT 1 FROM artist_tags at1 WHERE at1.artist_id = a.id)
               )
             ORDER BY name',
            [$lang]
        );
    }

    public static function countIncomplete(): int
    {
        return (int) (Database::getInstance()->fetchOne(
            'SELECT COUNT(*) AS n
             FROM artists a
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = a.id AND ai_fr.lang = "fr"
             WHERE a.moderation_status = "approved"
               AND (
                   ai_fr.bio IS NULL OR ai_fr.bio = "" OR a.start_year IS NULL
                   OR NOT EXISTS (SELECT 1 FROM artist_tags at1 WHERE at1.artist_id = a.id)
               )'
        )['n'] ?? 0);
    }

    public static function tagsFor(int $artistId, ?string $lang = null): array
    {
        $lang ??= Lang::current();

        return Database::getInstance()->fetchAll(
            'SELECT t.id, COALESCE(ti.name, ti_fr.name) AS name, tc.slug AS category_slug
             FROM artist_tags at
             JOIN tags t ON t.id = at.tag_id
             LEFT JOIN tags_i18n ti ON ti.tag_id = t.id AND ti.lang = ?
             LEFT JOIN tags_i18n ti_fr ON ti_fr.tag_id = t.id AND ti_fr.lang = "fr"
             LEFT JOIN tag_categories tc ON tc.id = t.category_id
             WHERE at.artist_id = ?',
            [$lang, $artistId]
        );
    }

    /**
     * @return int[]
     */
    public static function tagIdsFor(int $artistId): array
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT tag_id FROM artist_tags WHERE artist_id = ?',
            [$artistId]
        );

        return array_map(static fn (array $r): int => (int) $r['tag_id'], $rows);
    }

    /**
     * @param int[] $tagIds
     */
    public static function setTags(int $artistId, array $tagIds): void
    {
        $db = Database::getInstance();

        $db->query('DELETE FROM artist_tags WHERE artist_id = ?', [$artistId]);

        foreach (array_unique(array_map('intval', $tagIds)) as $tagId) {
            $db->query(
                'INSERT IGNORE INTO artist_tags (artist_id, tag_id) VALUES (?, ?)',
                [$artistId, $tagId]
            );
        }
    }

    public static function updateAvatar(int $id, ?string $avatarUrl): void
    {
        Database::getInstance()->query(
            'UPDATE artists SET avatar_path = ? WHERE id = ?',
            [$avatarUrl, $id]
        );
    }

    public static function updateSubscriberCount(int $id, ?int $count): void
    {
        Database::getInstance()->query(
            'UPDATE artists SET subscriber_count = ? WHERE id = ?',
            [$count, $id]
        );
    }
}
