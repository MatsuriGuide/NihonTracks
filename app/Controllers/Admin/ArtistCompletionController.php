<?php

namespace App\Controllers\Admin;

use App\Models\Artist;

class ArtistCompletionController extends AdminController
{
    public function index(): void
    {
        $this->render('admin/artist-completion/index', [
            'artists' => Artist::allIncomplete(),
        ]);
    }
}
