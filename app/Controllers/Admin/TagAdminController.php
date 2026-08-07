<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Slug;

class TagAdminController extends AdminController
{
    public function __construct()
    {
        // Surcharge : gestion des tags réservée aux admins, pas aux modérateurs
        Auth::requireRole('admin');
    }

    public function index(): void
    {
        $this->render('admin/tags/index', [
            'categories' => $this->categories(),
            'tags'       => $this->tags(),
        ]);
    }

    public function store(): void
    {
        $categoryId = (int) $this->input('category_id', 0);
        $nameFr = trim((string) $this->input('name_fr', ''));
        $nameEn = trim((string) $this->input('name_en', ''));
        $nameJa = trim((string) $this->input('name_ja', ''));

        $db = Database::getInstance();
        $categoryExists = $categoryId > 0
            && $db->fetchOne('SELECT id FROM tag_categories WHERE id = ?', [$categoryId]) !== null;

        if ($categoryExists && $nameFr !== '') {
            $slug = $this->uniqueSlug(Slug::make($nameFr));

            $db->query(
                'INSERT INTO tags (category_id, slug, created_by) VALUES (?, ?, ?)',
                [$categoryId, $slug, (int) Auth::id()]
            );
            $tagId = (int) $db->lastInsertId();

            $db->query('INSERT INTO tags_i18n (tag_id, lang, name) VALUES (?, "fr", ?)', [$tagId, $nameFr]);

            if ($nameEn !== '') {
                $db->query('INSERT INTO tags_i18n (tag_id, lang, name) VALUES (?, "en", ?)', [$tagId, $nameEn]);
            }

            if ($nameJa !== '') {
                $db->query('INSERT INTO tags_i18n (tag_id, lang, name) VALUES (?, "ja", ?)', [$tagId, $nameJa]);
            }
        }

        $this->redirect('/admin/tags');
    }

    public function delete(int $id): void
    {
        Database::getInstance()->query('DELETE FROM tags WHERE id = ?', [$id]);
        $this->redirect('/admin/tags');
    }

    private function categories(): array
    {
        return Database::getInstance()->fetchAll('SELECT * FROM tag_categories ORDER BY id');
    }

    private function tags(): array
    {
        $lang = \App\Core\Lang::current();

        return Database::getInstance()->fetchAll(
            'SELECT t.id, t.slug, t.category_id, COALESCE(ti.name, ti_fr.name) AS name
             FROM tags t
             LEFT JOIN tags_i18n ti ON ti.tag_id = t.id AND ti.lang = ?
             LEFT JOIN tags_i18n ti_fr ON ti_fr.tag_id = t.id AND ti_fr.lang = "fr"
             ORDER BY t.category_id, name',
            [$lang]
        );
    }

    private function uniqueSlug(string $base): string
    {
        $db = Database::getInstance();
        $slug = $base;
        $i = 2;

        while ($db->fetchOne('SELECT id FROM tags WHERE slug = ?', [$slug]) !== null) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
