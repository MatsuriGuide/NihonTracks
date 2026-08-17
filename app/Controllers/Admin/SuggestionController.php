<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\Artist;
use App\Models\Tag;
use App\Models\VideoSuggestion;

class SuggestionController extends AdminController
{
    public function index(): void
    {
        $this->render('admin/suggestions/index', [
            'suggestions' => VideoSuggestion::allPending(),
        ]);
    }

    public function dismiss(int $id): void
    {
        $suggestion = VideoSuggestion::findById($id);

        if ($suggestion && $suggestion['status'] === 'pending') {
            VideoSuggestion::dismiss($id, (int) Auth::id());
        }

        $this->redirect('/admin/suggestions');
    }

    /**
     * Affiche le formulaire vidéo complet, pré-rempli à partir de la
     * suggestion, pour compléter (type, tags) et publier. La suggestion
     * n'est marquée "imported" qu'une fois la vidéo réellement enregistrée
     * (cf. VideoController::store()), pas au simple clic sur ce lien.
     */
    public function publish(int $id): void
    {
        $suggestion = VideoSuggestion::findById($id);

        if (!$suggestion || $suggestion['status'] !== 'pending') {
            $this->redirect('/admin/suggestions');

            return;
        }

        $prefill = [
            'youtube_id'       => $suggestion['youtube_id'],
            'youtube_url'      => 'https://www.youtube.com/watch?v=' . $suggestion['youtube_id'],
            'title'            => $suggestion['title'],
            'thumbnail_url'    => $suggestion['thumbnail_url'],
            'channel_name'     => $suggestion['channel_name'],
            'release_date'     => $suggestion['published_at'],
            'duration_seconds' => null,
            'suggestion_id'    => $id,
        ];

        $this->render('videos/form', [
            'errors'            => [],
            'old'               => $prefill,
            'mode'              => 'create',
            'artists'           => Artist::all(),
            'tagGroups'         => Tag::selectable(),
            'selectedArtistIds' => [(int) $suggestion['artist_id']],
            'selectedTagIds'    => [],
            'autoDetected'      => true,
        ]);
    }
}
