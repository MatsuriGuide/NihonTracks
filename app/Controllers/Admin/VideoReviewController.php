<?php

namespace App\Controllers\Admin;

use App\Models\Video;

class VideoReviewController extends AdminController
{
    private const VIDEO_TYPES = ['mv', 'lyric_video', 'live', 'performance', 'cover', 'teaser', 'official_audio', 'other'];
    private const PER_PAGE = 24;

    public function index(): void
    {
        $total = Video::countNeedingReview();
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        $page = max(1, (int) $this->input('page', 1));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * self::PER_PAGE;

        $this->render('admin/video-review/index', [
            'videos'     => Video::allNeedingReview(null, self::PER_PAGE, $offset),
            'videoTypes' => self::VIDEO_TYPES,
            'page'       => $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Applique le type choisi (via le menu déroulant de la liste) et marque
     * la vidéo comme relue, en une seule action.
     */
    public function validate(int $id): void
    {
        $videoType = (string) $this->input('video_type', '');

        if (in_array($videoType, self::VIDEO_TYPES, true)) {
            Video::updateType($id, $videoType);
        }

        Video::markReviewed($id);

        $this->redirect('/admin/video-review');
    }
}
