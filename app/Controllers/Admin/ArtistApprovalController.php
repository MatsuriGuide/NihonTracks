<?php

namespace App\Controllers\Admin;

use App\Models\Artist;

class ArtistApprovalController extends AdminController
{
    public function index(): void
    {
        $this->render('admin/artist-approvals/index', [
            'pending' => Artist::allPending(),
        ]);
    }

    public function approve(int $id): void
    {
        $artist = Artist::findById($id);

        if ($artist && $artist['moderation_status'] === 'pending') {
            Artist::approve($id);
        }

        $this->redirect('/admin/artist-approvals');
    }

    public function reject(int $id): void
    {
        $artist = Artist::findById($id);

        if ($artist && $artist['moderation_status'] === 'pending') {
            Artist::reject($id);
        }

        $this->redirect('/admin/artist-approvals');
    }
}
