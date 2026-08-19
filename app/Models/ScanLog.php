<?php

namespace App\Models;

use App\Core\Database;

class ScanLog
{
    public static function record(string $source, int $channelsScanned, int $suggestionsFound, int $errorsCount, ?array $details = null): void
    {
        Database::getInstance()->query(
            'INSERT INTO scan_log (source, channels_scanned, suggestions_found, errors_count, details)
             VALUES (?, ?, ?, ?, ?)',
            [
                $source,
                $channelsScanned,
                $suggestionsFound,
                $errorsCount,
                $details !== null ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            ]
        );
    }

    public static function recordBlockedAccess(string $ip, ?string $userAgent): void
    {
        self::record('blocked_access', 0, 0, 0, ['ip' => $ip, 'user_agent' => $userAgent]);
    }

    public static function recent(int $limit = 20): array
    {
        // Concaténation directe plutôt que paramètre lié : LIMIT posait des
        // soucis de type avec certains pilotes PDO en préparation native.
        $limit = max(1, min(200, $limit));

        return Database::getInstance()->fetchAll(
            'SELECT * FROM scan_log ORDER BY run_at DESC LIMIT ' . $limit
        );
    }
}
