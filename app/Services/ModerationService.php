<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Artist;
use App\Models\Report;
use App\Models\Video;

class ModerationService
{
    public static function report(string $type, int $id, int $reportedBy, string $reason, ?string $comment): void
    {
        Report::create($type, $id, $reportedBy, $reason, $comment);
    }

    /**
     * Seul point d'entrée permettant à un modérateur d'agir sur le contenu
     * d'un autre utilisateur — toujours via un signalement précis, jamais
     * en modification libre (cf. règle définie dans l'architecture).
     *
     * $action : "hide" (vidéos uniquement) ou "delete".
     */
    public static function resolve(int $reportId, string $action, int $moderatorId): void
    {
        $report = Report::findById($reportId);

        if (!$report || $report['status'] !== 'pending') {
            return;
        }

        $type = $report['reportable_type'];
        $id = (int) $report['reportable_id'];

        if ($action === 'hide' && $type === 'video') {
            Database::getInstance()->query('UPDATE videos SET status = "hidden" WHERE id = ?', [$id]);
            EditHistoryService::log('video', $id, $moderatorId, 'update', [
                'status'    => 'hidden',
                'via_report' => $reportId,
            ]);
        } elseif ($action === 'delete') {
            match ($type) {
                'video'  => Video::delete($id),
                'artist' => Artist::delete($id),
                'tag'    => Database::getInstance()->query('DELETE FROM tags WHERE id = ?', [$id]),
                default  => null,
            };
            EditHistoryService::log($type, $id, $moderatorId, 'delete', ['via_report' => $reportId]);
        }

        Report::markResolved($reportId, $moderatorId);
    }

    public static function dismiss(int $reportId, int $moderatorId): void
    {
        Report::markDismissed($reportId, $moderatorId);
    }
}
