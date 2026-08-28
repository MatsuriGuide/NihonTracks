<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Lang;

class Artist
{
    /**
     * Liste publique : uniquement les artistes approuvés. Utilisé partout
     * où le contenu doit être visible/sélectionnable (accueil, listing,
     * formulaires vidéo, relations...).
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

    /**
     * Retourne les traductions existantes d'un artiste, indexées par langue.
     */
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
     * ou français) correspond exactement au nom de la chaîne YouTube
     * (insensible à la casse).
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

    /**
     * @param string $moderationStatus "approved" ou "pending" — décidé par
     *   l'appelant selon le rôle de l'utilisateur (mod/admin = approved
     *   immédiat, utilisateur normal = pending).
     */
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

    public static function delete(int $id): void
    {
        Database::getInstance()->query('DELETE FROM artists WHERE id = ?', [$id]);
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
        // Conservé en base (comme les signalements/suggestions rejetés),
        // simplement caché — pas de suppression définitive.
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

    /**
     * Artistes approuvés à qui il manque une bio et/ou une année de début —
     * outil de suivi pour compléter progressivement le catalogue.
     */
    public static function allIncomplete(?string $lang = null): array
    {
        $lang ??= Lang::current();

        return Database::getInstance()->fetchAll(
            'SELECT a.id, a.slug, a.start_year,
                    COALESCE(ai.name, ai_fr.name) AS name,
                    ai_fr.bio AS bio_fr
             FROM artists a
             LEFT JOIN artists_i18n ai ON ai.artist_id = a.id AND ai.lang = ?
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = a.id AND ai_fr.lang = "fr"
             WHERE a.moderation_status = "approved"
               AND (ai_fr.bio IS NULL OR ai_fr.bio = "" OR a.start_year IS NULL)
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
               AND (ai_fr.bio IS NULL OR ai_fr.bio = "" OR a.start_year IS NULL)'
        )['n'] ?? 0);
    }
}
