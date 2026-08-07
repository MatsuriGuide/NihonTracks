<?php

namespace App\Controllers\Admin;

use App\Core\Database;

class DashboardController extends AdminController
{
    public function index(): void
    {
        $db = Database::getInstance();

        $pendingReports = $db->fetchOne(
            'SELECT COUNT(*) AS n FROM reports WHERE status = "pending"'
        )['n'] ?? 0;

        $this->render('admin/dashboard', [
            'pendingReports' => $pendingReports,
        ]);
    }
}
