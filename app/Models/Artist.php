<?php

namespace App\Models;

use App\Core\Database;

class Artist
{
    public static function all(?string $lang = null): array
    {
        $lang ??= \App\Core\Lang::current();

        return Database::getInstance()->fetchAll(
            'SELECT a.id, a.slug, a.type, a.status,
                    COALESCE(ai.name, ai_fr.name) AS name
             FROM artists a
             LEFT JOIN artists_i18n ai ON ai.artist_id = a.id AND ai.lang = ?
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = a.id AND ai_fr.lang = "fr"
             ORDER BY name',
            [$lang]
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::getInstance()->fetchOne(
            'SELECT * FROM artists WHERE id = ?',
            [$id]
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::getInstance()->fetchOne(
            'SELECT * FROM artists WHERE slug = ?',
            [$slug]
        );
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

    public static function create(array $data, string $name, ?string $bio, int $createdBy): int
    {
        $db = Database::getInstance();

        $db->query(
            'INSERT INTO artists (type, status, start_year, end_year, label, slug, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $data['type'],
                $data['status'],
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

    public static function delete(int $id): void
    {
        Database::getInstance()->query('DELETE FROM artists WHERE id = ?', [$id]);
    }
}
