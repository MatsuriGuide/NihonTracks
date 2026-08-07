<?php

namespace App\Models;

use App\Core\Database;

class Tag
{
    /**
     * Tags des catégories "genre" et "language", groupés par catégorie,
     * traduits dans la langue donnée.
     */
    public static function selectable(string $lang = 'fr'): array
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT t.id, t.slug, tc.slug AS category_slug, ti.name
             FROM tags t
             JOIN tag_categories tc ON tc.id = t.category_id
             LEFT JOIN tags_i18n ti ON ti.tag_id = t.id AND ti.lang = ?
             WHERE tc.slug IN ("genre", "language")
             ORDER BY tc.slug, ti.name',
            [$lang]
        );

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['category_slug']][] = $row;
        }

        return $grouped;
    }
}
