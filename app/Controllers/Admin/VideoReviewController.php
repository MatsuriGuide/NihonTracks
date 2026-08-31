<?php

namespace App\Controllers\Admin;

use App\Models\Video;

class VideoReviewController extends AdminController
{
    private const VIDEO_TYPES = ['mv', 'lyric_video', 'live', 'performance', 'cover', 'teaser', 'official_audio', 'other'];

    public function index(): void
    {
        $this->render('admin/video-review/index', [
            'videos'     => Video::allNeedingReview(),
            'videoTypes' => self::VIDEO_TYPES,
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
