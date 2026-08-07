<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Services\ModerationService;

class ReportController extends Controller
{
    public function store(): void
    {
        Auth::requireLogin();

        $type = (string) $this->input('reportable_type', '');
        $id = (int) $this->input('reportable_id', 0);
        $reason = (string) $this->input('reason', 'other');
        $comment = trim((string) $this->input('comment', '')) ?: null;

        $validTypes = ['video', 'artist', 'tag'];
        $validReasons = ['duplicate', 'wrong_info', 'spam', 'inappropriate', 'other'];

        if (in_array($type, $validTypes, true) && $id > 0 && in_array($reason, $validReasons, true)) {
            ModerationService::report($type, $id, (int) Auth::id(), $reason, $comment);
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
}
