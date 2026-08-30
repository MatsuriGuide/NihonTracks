<?php

namespace App\Controllers\Admin;

use App\Models\Video;

class VideoReviewController extends AdminController
{
    public function index(): void
    {
        $this->render('admin/video-review/index', [
            'videos' => Video::allNeedingReview(),
        ]);
    }

    public function markReviewed(int $id): void
    {
        Video::markReviewed($id);
        $this->redirect('/admin/video-review');
    }
}
