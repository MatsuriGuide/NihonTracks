<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Report;
use App\Services\ModerationService;

class ReportController extends AdminController
{
    public function index(): void
    {
        $pending = array_map([$this, 'withLabel'], Report::allPending());
        $resolved = array_map([$this, 'withLabel'], Report::allResolved());

        $this->render('admin/reports/index', [
            'pending'  => $pending,
            'resolved' => $resolved,
        ]);
    }

    public function resolve(int $id): void
    {
        $action = (string) $this->input('action', '');

        if (!in_array($action, ['hide', 'delete', 'dismiss'], true)) {
            $this->redirect('/admin/reports');

            return;
        }

        if ($action === 'dismiss') {
            ModerationService::dismiss($id, (int) Auth::id());
        } else {
            ModerationService::resolve($id, $action, (int) Auth::id());
        }

        $this->redirect('/admin/reports');
    }

    /**
     * Enrichit un signalement avec un libellé et un lien lisibles vers le
     * contenu concerné (le contenu peut avoir été supprimé entre-temps).
     */
    private function withLabel(array $report): array
    {
        $db = Database::getInstance();
        $id = (int) $report['reportable_id'];

        switch ($report['reportable_type']) {
            case 'video':
                $row = $db->fetchOne(
                    'SELECT title FROM videos_i18n WHERE video_id = ? AND lang = "fr"',
                    [$id]
                );
                $report['content_label'] = $row['title'] ?? ('Vidéo #' . $id);
                $report['content_url'] = url('/videos/' . $id);

                break;

            case 'artist':
                $row = $db->fetchOne(
                    'SELECT ai.name, a.slug
                     FROM artists a
                     LEFT JOIN artists_i18n ai ON ai.artist_id = a.id AND ai.lang = "fr"
                     WHERE a.id = ?',
                    [$id]
                );
                $report['content_label'] = $row['name'] ?? ('Artiste #' . $id);
                $report['content_url'] = $row ? url('/artists/' . $row['slug']) : null;

                break;

            case 'tag':
                $row = $db->fetchOne(
                    'SELECT name FROM tags_i18n WHERE tag_id = ? AND lang = "fr"',
                    [$id]
                );
                $report['content_label'] = $row['name'] ?? ('Tag #' . $id);
                $report['content_url'] = null;

                break;

            default:
                $report['content_label'] = 'Contenu #' . $id;
                $report['content_url'] = null;
        }

        return $report;
    }
}
