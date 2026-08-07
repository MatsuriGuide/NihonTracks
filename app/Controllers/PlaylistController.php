<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Playlist;
use App\Models\Video;

class PlaylistController extends Controller
{
    public function index(): void
    {
        $this->render('playlists/index', ['playlists' => Playlist::allPublic()]);
    }

    public function mine(): void
    {
        Auth::requireLogin();
        $this->render('playlists/mine', ['playlists' => Playlist::allByUser((int) Auth::id())]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->render('playlists/form', ['errors' => [], 'old' => [], 'mode' => 'create']);
    }

    public function store(): void
    {
        Auth::requireLogin();

        [$data, $errors] = $this->validate();

        if (!empty($errors)) {
            $this->render('playlists/form', ['errors' => $errors, 'old' => $data, 'mode' => 'create']);

            return;
        }

        $playlistId = Playlist::create($data['name'], $data['description'], $data['is_public'], (int) Auth::id());

        $this->redirect('/playlists/' . $playlistId);
    }

    public function show(int $id): void
    {
        $playlist = Playlist::findById($id);

        if (!$playlist) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';

            return;
        }

        $canView = (bool) $playlist['is_public'] || Auth::canEdit((int) $playlist['user_id']);

        if (!$canView) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        $videos = Playlist::videosFor($id);

        $availableVideos = [];
        if (Auth::canEdit((int) $playlist['user_id'])) {
            $existingIds = array_column($videos, 'id');
            $availableVideos = array_values(array_filter(
                Video::all(),
                fn (array $v): bool => !in_array((int) $v['id'], $existingIds, true)
            ));
        }

        $this->render('playlists/show', [
            'playlist'        => $playlist,
            'videos'          => $videos,
            'availableVideos' => $availableVideos,
        ]);
    }

    public function edit(int $id): void
    {
        Auth::requireLogin();

        $playlist = Playlist::findById($id);

        if (!$playlist || !Auth::canEdit((int) $playlist['user_id'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        $this->render('playlists/form', [
            'errors'     => [],
            'old'        => $playlist,
            'mode'       => 'edit',
            'playlistId' => $id,
        ]);
    }

    public function update(int $id): void
    {
        Auth::requireLogin();

        $playlist = Playlist::findById($id);

        if (!$playlist || !Auth::canEdit((int) $playlist['user_id'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        [$data, $errors] = $this->validate();

        if (!empty($errors)) {
            $this->render('playlists/form', [
                'errors'     => $errors,
                'old'        => $data,
                'mode'       => 'edit',
                'playlistId' => $id,
            ]);

            return;
        }

        Playlist::update($id, $data['name'], $data['description'], $data['is_public']);

        $this->redirect('/playlists/' . $id);
    }

    public function delete(int $id): void
    {
        Auth::requireLogin();

        $playlist = Playlist::findById($id);

        if (!$playlist || !Auth::canEdit((int) $playlist['user_id'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        Playlist::delete($id);
        $this->redirect('/playlists/mine');
    }

    public function addVideo(int $id): void
    {
        Auth::requireLogin();

        $playlist = Playlist::findById($id);

        if (!$playlist || !Auth::canEdit((int) $playlist['user_id'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        $videoId = (int) $this->input('video_id', 0);

        if ($videoId > 0) {
            Playlist::addVideo($id, $videoId);
        }

        $this->redirect('/playlists/' . $id);
    }

    public function removeVideo(int $id, int $videoId): void
    {
        Auth::requireLogin();

        $playlist = Playlist::findById($id);

        if (!$playlist || !Auth::canEdit((int) $playlist['user_id'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        Playlist::removeVideo($id, $videoId);
        $this->redirect('/playlists/' . $id);
    }

    /**
     * @return array{0: array, 1: string[]}
     */
    private function validate(): array
    {
        $data = [
            'name'        => trim((string) $this->input('name', '')),
            'description' => trim((string) $this->input('description', '')) ?: null,
            'is_public'   => (bool) $this->input('is_public', false),
        ];

        $errors = [];

        if ($data['name'] === '') {
            $errors[] = t('playlists.error.name_required');
        }

        return [$data, $errors];
    }
}
