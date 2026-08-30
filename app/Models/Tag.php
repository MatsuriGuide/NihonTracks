<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Lang;

class Tag
{
    /**
     * Tags des catégories "genre", "language" et "vocalist", groupés par
     * catégorie (avec le libellé traduit de la catégorie elle-même, pas
     * seulement son slug technique), traduits dans la langue donnée.
     *
     * @return array<string, array{label: string, tags: array}>
     */
    public static function selectable(?string $lang = null): array
    {
        $lang ??= Lang::current();

        $rows = Database::getInstance()->fetchAll(
            'SELECT t.id, t.slug, tc.slug AS category_slug,
                    COALESCE(tci.name, tci_fr.name) AS category_label,
                    COALESCE(ti.name, ti_fr.name) AS name
             FROM tags t
             JOIN tag_categories tc ON tc.id = t.category_id
             LEFT JOIN tag_categories_i18n tci ON tci.category_id = tc.id AND tci.lang = ?
             LEFT JOIN tag_categories_i18n tci_fr ON tci_fr.category_id = tc.id AND tci_fr.lang = "fr"
             LEFT JOIN tags_i18n ti ON ti.tag_id = t.id AND ti.lang = ?
             LEFT JOIN tags_i18n ti_fr ON ti_fr.tag_id = t.id AND ti_fr.lang = "fr"
             WHERE tc.slug IN ("genre", "language", "vocalist")
             ORDER BY tc.slug, name',
            [$lang, $lang]
        );

        $grouped = [];
        foreach ($rows as $row) {
            if (!isset($grouped[$row['category_slug']])) {
                $grouped[$row['category_slug']] = [
                    'label' => $row['category_label'] ?? ucfirst($row['category_slug']),
                    'tags'  => [],
                ];
            }
            $grouped[$row['category_slug']]['tags'][] = $row;
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
