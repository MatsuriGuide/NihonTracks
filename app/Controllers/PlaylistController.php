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

        $this->render('playlists/show', [
            'playlist' => $playlist,
            'videos'   => Playlist::videosFor($id),
            'canEdit'  => Auth::canEdit((int) $playlist['user_id']),
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
     * Endpoint AJAX (JSON) : recherche de vidéos par titre pour l'ajout à
     * la playlist — évite de charger toutes les vidéos d'un coup (des
     * milliers), ne renvoie que quelques résultats correspondant à la
     * frappe en cours, en excluant celles déjà présentes dans la playlist.
     */
    public function searchVideos(int $id): void
    {
        $playlist = Playlist::findById($id);

        if (!$playlist || !Auth::canEdit((int) $playlist['user_id'])) {
            $this->json(['error' => 'forbidden'], 403);

            return;
        }

        $query = trim((string) $this->input('q', ''));

        if ($query === '') {
            $this->json(['results' => []]);

            return;
        }

        $existingIds = array_column(Playlist::videosFor($id), 'id');

        $results = Video::searchByTitle($query, $existingIds, 15);

        $this->json(['results' => $results]);
    }

    /**
     * Endpoint AJAX (JSON) : ajoute la vidéo si elle n'y est pas encore,
     * la retire sinon. Utilisé par le bouton d'ajout rapide sur la fiche
     * vidéo, sans rechargement de page.
     */
    public function toggleVideo(int $id, int $videoId): void
    {
        if (!Auth::check()) {
            $this->json(['error' => 'unauthorized'], 401);

            return;
        }

        $playlist = Playlist::findById($id);

        if (!$playlist || !Auth::canEdit((int) $playlist['user_id'])) {
            $this->json(['error' => 'forbidden'], 403);

            return;
        }

        if (Playlist::containsVideo($id, $videoId)) {
            Playlist::removeVideo($id, $videoId);
            $this->json(['added' => false]);

            return;
        }

        Playlist::addVideo($id, $videoId);
        $this->json(['added' => true]);
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
