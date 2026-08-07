<?php

namespace App\Services;

use App\Core\Database;

class EditHistoryService
{
    public static function log(string $type, int $id, int $editorId, string $action, array $diff = []): void
    {
        Database::getInstance()->query(
            'INSERT INTO edit_history (editable_type, editable_id, editor_id, action, diff_json)
             VALUES (?, ?, ?, ?, ?)',
            [$type, $id, $editorId, $action, json_encode($diff, JSON_UNESCAPED_UNICODE)]
        );
    }
}
