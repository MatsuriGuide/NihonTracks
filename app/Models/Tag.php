<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Lang;

class Tag
{
    /**
     * Tags des catégories "genre" et "language", groupés par catégorie,
     * traduits dans la langue donnée.
     */
    public static function selectable(?string $lang = null): array
    {
        $lang ??= Lang::current();

        $rows = Database::getInstance()->fetchAll(
            'SELECT t.id, t.slug, tc.slug AS category_slug,
                    COALESCE(ti.name, ti_fr.name) AS name
             FROM tags t
             JOIN tag_categories tc ON tc.id = t.category_id
             LEFT JOIN tags_i18n ti ON ti.tag_id = t.id AND ti.lang = ?
             LEFT JOIN tags_i18n ti_fr ON ti_fr.tag_id = t.id AND ti_fr.lang = "fr"
             WHERE tc.slug IN ("genre", "language")
             ORDER BY tc.slug, name',
            [$lang]
        );

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['category_slug']][] = $row;
        }

        return $grouped;
    }

    /**
     * Libellés français des tags "genre", indexés par tag_id — utilisé
     * comme référentiel fixe pour la suggestion de tags par IA (le
     * français sert de langue pivot, toujours renseignée par défaut).
     *
     * @return array<int, string>
     */
    public static function genreLabelsFr(): array
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT t.id, ti_fr.name
             FROM tags t
             JOIN tag_categories tc ON tc.id = t.category_id AND tc.slug = "genre"
             LEFT JOIN tags_i18n ti_fr ON ti_fr.tag_id = t.id AND ti_fr.lang = "fr"
             WHERE ti_fr.name IS NOT NULL'
        );

        $labels = [];
        foreach ($rows as $row) {
            $labels[(int) $row['id']] = $row['name'];
        }

        return $labels;
    }
}
