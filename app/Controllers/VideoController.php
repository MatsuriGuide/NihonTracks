<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Tag;
use App\Models\Video;
use App\Services\YoutubeApiService;

class VideoController extends Controller
{
    public function index(): void
    {
        $videos = Video::all();
        $this->render('videos/index', ['videos' => $videos]);
    }

    public function show(int $id): void
    {
        $video = Video::findById($id);

        if (!$video) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';

            return;
        }

        $this->render('videos/show', [
            'video'         => $video,
            'translation'   => Video::translation($id),
            'artists'       => Video::artistsFor($id),
            'tags'          => Video::tagsFor($id),
            'userPlaylists' => Auth::check() ? Playlist::allByUser((int) Auth::id()) : [],
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->render('videos/create_url', ['error' => null]);
    }

    /**
     * Étape 1 : on colle une URL YouTube. On tente la récupération auto des
     * métadonnées ; en cas d'échec (quota, clé absente...), on affiche quand
     * même le formulaire complet, vide, pour saisie manuelle.
     */
    public function preview(): void
    {
        Auth::requireLogin();

        $url = trim((string) $this->input('youtube_url', ''));
        $youtubeId = YoutubeApiService::extractVideoId($url);

        if ($youtubeId === null) {
            $this->render('videos/create_url', [
                'error' => t('videos.bad_url'),
            ]);

            return;
        }

        if (Video::findByYoutubeId($youtubeId)) {
            $this->render('videos/create_url', [
                'error' => t('videos.already_exists'),
            ]);

            return;
        }

        $metadata = YoutubeApiService::fetchMetadata($youtubeId);

        $prefill = $metadata ?? [
            'youtube_id'       => $youtubeId,
            'title'            => '',
            'channel_name'     => null,
            'release_date'     => null,
            'thumbnail_url'    => null,
            'duration_seconds' => null,
        ];
        $prefill['youtube_url'] = 'https://www.youtube.com/watch?v=' . $youtubeId;

        $this->render('videos/form', [
            'errors'            => $metadata === null
                ? [t('videos.api_fallback')]
                : [],
            'old'               => $prefill,
            'mode'              => 'create',
            'artists'           => Artist::all(),
            'tagGroups'         => Tag::selectable(),
            'selectedArtistIds' => [],
            'selectedTagIds'    => [],
        ]);
    }

    public function store(): void
    {
        Auth::requireLogin();

        [$data, $errors] = $this->validate();

        if ($data['youtube_id'] !== '' && Video::findByYoutubeId($data['youtube_id'])) {
            $errors[] = t('videos.already_exists');
        }

        $artistIds = array_map('intval', (array) $this->input('artist_ids', []));
        $tagIds = array_map('intval', (array) $this->input('tag_ids', []));

        if (empty($artistIds)) {
            $errors[] = t('videos.error.select_artist');
        }

        if (!empty($errors)) {
            $this->render('videos/form', [
                'errors'            => $errors,
                'old'               => $data,
                'mode'              => 'create',
                'artists'           => Artist::all(),
                'tagGroups'         => Tag::selectable(),
                'selectedArtistIds' => $artistIds,
                'selectedTagIds'    => $tagIds,
            ]);

            return;
        }

        $videoId = Video::create($data, $artistIds, $tagIds, (int) Auth::id());

        $this->redirect('/videos/' . $videoId);
    }

    public function edit(int $id): void
    {
        Auth::requireLogin();

        $video = Video::findById($id);

        if (!$video || !Auth::canEdit((int) $video['added_by'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        $translation = Video::translation($id) ?? ['title' => ''];
        $old = array_merge($video, ['title' => $translation['title']]);

        $artists = Video::artistsFor($id);
        $tags = Video::tagsFor($id);

        $this->render('videos/form', [
            'errors'            => [],
            'old'               => $old,
            'mode'              => 'edit',
            'videoId'           => $id,
            'artists'           => Artist::all(),
            'tagGroups'         => Tag::selectable(),
            'selectedArtistIds' => array_column($artists, 'id'),
            'selectedTagIds'    => array_column($tags, 'id'),
        ]);
    }

    public function update(int $id): void
    {
        Auth::requireLogin();

        $video = Video::findById($id);

        if (!$video || !Auth::canEdit((int) $video['added_by'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        [$data, $errors] = $this->validate();

        // Champs liés à YouTube : non modifiables depuis ce formulaire
        $data['youtube_id']       = $video['youtube_id'];
        $data['youtube_url']      = $video['youtube_url'];
        $data['thumbnail_url']    = $video['thumbnail_url'];
        $data['channel_name']     = $video['channel_name'];
        $data['duration_seconds'] = $video['duration_seconds'];

        $artistIds = array_map('intval', (array) $this->input('artist_ids', []));
        $tagIds = array_map('intval', (array) $this->input('tag_ids', []));

        if (empty($artistIds)) {
            $errors[] = t('videos.error.select_artist');
        }

        if (!empty($errors)) {
            $this->render('videos/form', [
                'errors'            => $errors,
                'old'               => $data,
                'mode'              => 'edit',
                'videoId'           => $id,
                'artists'           => Artist::all(),
                'tagGroups'         => Tag::selectable(),
                'selectedArtistIds' => $artistIds,
                'selectedTagIds'    => $tagIds,
            ]);

            return;
        }

        Video::update($id, $data, $artistIds, $tagIds);

        $this->redirect('/videos/' . $id);
    }

    public function delete(int $id): void
    {
        Auth::requireLogin();

        $video = Video::findById($id);

        if (!$video || !Auth::canEdit((int) $video['added_by'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        Video::delete($id);
        $this->redirect('/videos');
    }

    /**
     * @return array{0: array, 1: string[]}
     */
    private function validate(): array
    {
        $data = [
            'youtube_id'       => (string) $this->input('youtube_id', ''),
            'youtube_url'      => (string) $this->input('youtube_url', ''),
            'title'            => trim((string) $this->input('title', '')),
            'release_date'     => (string) $this->input('release_date', '') ?: null,
            'video_type'       => (string) $this->input('video_type', 'mv'),
            'thumbnail_url'    => (string) $this->input('thumbnail_url', '') ?: null,
            'channel_name'     => (string) $this->input('channel_name', '') ?: null,
            'duration_seconds' => ((int) $this->input('duration_seconds', 0)) ?: null,
        ];

        $errors = [];

        if ($data['youtube_id'] === '') {
            $errors[] = 'Identifiant YouTube manquant.';
        }

        if ($data['title'] === '') {
            $errors[] = t('videos.error.title_required');
        }

        $validTypes = ['mv', 'lyric_video', 'live', 'performance', 'cover', 'teaser', 'other'];
        if (!in_array($data['video_type'], $validTypes, true)) {
            $errors[] = t('videos.error.type_invalid');
        }

        return [$data, $errors];
    }
}
