<?php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Services\YoutubeApiService;

/**
 * Répare les vidéos dont le titre est un texte de remplacement renvoyé par
 * YouTube ("Deleted video", "Private video") au lieu du vrai titre.
 */
class TitleRepairController extends AdminController
{
    private const PLACEHOLDERS = ['Deleted video', 'Private video'];
    private const BATCH_SIZE = 100;

    public function index(): void
    {
        $this->renderPage(null);
    }

    public function run(): void
    {
        $db = Database::getInstance();

        $rows = $db->fetchAll(
            'SELECT vi.id AS i18n_id, v.youtube_id
             FROM videos_i18n vi
             JOIN videos v ON v.id = vi.video_id
             WHERE vi.title IN ("Deleted video", "Private video")
             ORDER BY vi.id
             LIMIT ' . (int) self::BATCH_SIZE
        );

        $details = YoutubeApiService::fetchVideosDetails(array_column($rows, 'youtube_id'));

        $fixed = 0;
        foreach ($rows as $row) {
            $title = $details[$row['youtube_id']]['title'] ?? null;

            if ($title === null || trim($title) === '' || in_array($title, self::PLACEHOLDERS, true)) {
                continue;
            }

            $db->query('UPDATE videos_i18n SET title = ? WHERE id = ?', [$title, $row['i18n_id']]);
            $fixed++;
        }

        // Miniatures manquantes (même cause : la liste "uploads" ne les fournit
        // pas pour ces vidéos). L'URL standard YouTube ne demande aucun appel API.
        $thumbs = $db->query(
            'UPDATE videos
             SET thumbnail_url = CONCAT("https://i.ytimg.com/vi/", youtube_id, "/hqdefault.jpg")
             WHERE thumbnail_url IS NULL OR thumbnail_url = ""'
        )->rowCount();

        $this->renderPage(['processed' => count($rows), 'fixed' => $fixed, 'thumbs' => $thumbs]);
    }

    private function renderPage(?array $result): void
    {
        $count = (int) (Database::getInstance()->fetchOne(
            'SELECT COUNT(*) AS n FROM videos_i18n WHERE title IN ("Deleted video", "Private video")'
        )['n'] ?? 0);

        $missingThumbs = (int) (Database::getInstance()->fetchOne(
            'SELECT COUNT(*) AS n FROM videos WHERE thumbnail_url IS NULL OR thumbnail_url = ""'
        )['n'] ?? 0);

        $this->render('admin/title-repair/index', [
            'result'        => $result,
            'remaining'     => $count,
            'missingThumbs' => $missingThumbs,
        ]);
    }
}
