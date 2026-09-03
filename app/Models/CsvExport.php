<?php

namespace App\Models;

use App\Core\Database;

class CsvExport
{
    /**
     * Date/heure du dernier export réalisé (tous utilisateurs confondus),
     * au format DATETIME MySQL — null si aucun export n'a jamais été fait.
     */
    public static function lastExportedAt(): ?string
    {
        $row = Database::getInstance()->fetchOne(
            'SELECT exported_at FROM csv_exports ORDER BY exported_at DESC LIMIT 1'
        );

        return $row['exported_at'] ?? null;
    }

    public static function record(int $userId): void
    {
        Database::getInstance()->query(
            'INSERT INTO csv_exports (exported_by, exported_at) VALUES (?, NOW())',
            [$userId]
        );
    }
}
