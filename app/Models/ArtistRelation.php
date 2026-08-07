<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Lang;

class ArtistRelation
{
    public static function create(int $artistId, int $relatedArtistId, string $type, ?string $note): int
    {
        $db = Database::getInstance();

        $db->query(
            'INSERT INTO artist_relations (artist_id, related_artist_id, relation_type, note) VALUES (?, ?, ?, ?)',
            [$artistId, $relatedArtistId, $type, $note]
        );

        return (int) $db->lastInsertId();
    }

    public static function findById(int $id): ?array
    {
        return Database::getInstance()->fetchOne('SELECT * FROM artist_relations WHERE id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        Database::getInstance()->query('DELETE FROM artist_relations WHERE id = ?', [$id]);
    }

    /**
     * Relations où cet artiste est la "source" (ex : la personne dans member_of).
     */
    public static function outgoing(int $artistId, ?string $lang = null): array
    {
        $lang ??= Lang::current();

        return Database::getInstance()->fetchAll(
            'SELECT ar.id, ar.relation_type, ar.note, a.id AS other_id, a.slug AS other_slug,
                    COALESCE(ai.name, ai_fr.name) AS other_name
             FROM artist_relations ar
             JOIN artists a ON a.id = ar.related_artist_id
             LEFT JOIN artists_i18n ai ON ai.artist_id = a.id AND ai.lang = ?
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = a.id AND ai_fr.lang = "fr"
             WHERE ar.artist_id = ?',
            [$lang, $artistId]
        );
    }

    /**
     * Relations où cet artiste est la "cible" (ex : le groupe dans member_of).
     */
    public static function incoming(int $artistId, ?string $lang = null): array
    {
        $lang ??= Lang::current();

        return Database::getInstance()->fetchAll(
            'SELECT ar.id, ar.relation_type, ar.note, a.id AS other_id, a.slug AS other_slug,
                    COALESCE(ai.name, ai_fr.name) AS other_name
             FROM artist_relations ar
             JOIN artists a ON a.id = ar.artist_id
             LEFT JOIN artists_i18n ai ON ai.artist_id = a.id AND ai.lang = ?
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = a.id AND ai_fr.lang = "fr"
             WHERE ar.related_artist_id = ?',
            [$lang, $artistId]
        );
    }
}
