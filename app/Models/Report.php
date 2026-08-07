<?php

namespace App\Models;

use App\Core\Database;

class Report
{
    public static function create(string $type, int $id, int $reportedBy, string $reason, ?string $comment): int
    {
        $db = Database::getInstance();

        $db->query(
            'INSERT INTO reports (reportable_type, reportable_id, reported_by, reason, comment)
             VALUES (?, ?, ?, ?, ?)',
            [$type, $id, $reportedBy, $reason, $comment]
        );

        return (int) $db->lastInsertId();
    }

    public static function findById(int $id): ?array
    {
        return Database::getInstance()->fetchOne('SELECT * FROM reports WHERE id = ?', [$id]);
    }

    public static function allPending(): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT r.*, u.display_name AS reporter_name
             FROM reports r
             JOIN users u ON u.id = r.reported_by
             WHERE r.status = "pending"
             ORDER BY r.created_at ASC'
        );
    }

    public static function allResolved(int $limit = 50): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT r.*, u.display_name AS reporter_name, m.display_name AS resolver_name
             FROM reports r
             JOIN users u ON u.id = r.reported_by
             LEFT JOIN users m ON m.id = r.resolved_by
             WHERE r.status != "pending"
             ORDER BY r.resolved_at DESC
             LIMIT ?',
            [$limit]
        );
    }

    public static function markResolved(int $id, int $moderatorId): void
    {
        Database::getInstance()->query(
            'UPDATE reports SET status = "resolved", resolved_by = ?, resolved_at = NOW() WHERE id = ?',
            [$moderatorId, $id]
        );
    }

    public static function markDismissed(int $id, int $moderatorId): void
    {
        Database::getInstance()->query(
            'UPDATE reports SET status = "dismissed", resolved_by = ?, resolved_at = NOW() WHERE id = ?',
            [$moderatorId, $id]
        );
    }
}
