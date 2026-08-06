<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;

class TagAdminController extends AdminController
{
    public function __construct()
    {
        // Surcharge : gestion des tags réservée aux admins, pas aux modérateurs
        Auth::requireRole('admin');
    }

    public function index(): void
    {
        $db = Database::getInstance();

        $categories = $db->fetchAll('SELECT * FROM tag_categories ORDER BY id');
        $tags = $db->fetchAll(
            'SELECT t.id, t.slug, t.category_id, ti.name
             FROM tags t
             LEFT JOIN tags_i18n ti ON ti.tag_id = t.id AND ti.lang = "fr"
             ORDER BY t.category_id, ti.name'
        );

        $this->render('admin/tags/index', [
            'categories' => $categories,
            'tags'       => $tags,
        ]);
    }
}
