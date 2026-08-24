<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Slug;
use App\Models\Artist;
use App\Models\ArtistLink;
use App\Models\ArtistRelation;
use App\Models\Video;
use App\Services\YoutubeApiService;

class ArtistController extends Controller
{
    public function index(): void
    {
        $artists = Artist::all();
        $this->render('artists/index', ['artists' => $artists]);
    }

    public function show(string $slug): void
    {
        $artist = Artist::findBySlug($slug);

        if (!$artist) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';

            return;
        }

        $canEdit = Auth::canEdit((int) $artist['created_by']);
        $editMode = $canEdit && $this->input('edit', '0') === '1';

        $translations = Artist::translations((int) $artist['id']);
        $lang = \App\Core\Lang::current();

        $this->render('artists/show', [
            'artist'            => $artist,
            'translation'       => $translations[$lang] ?? $translations['fr'] ?? null,
            'allTranslations'   => $translations,
            'outgoingRelations' => ArtistRelation::outgoing((int) $artist['id']),
            'incomingRelations' => ArtistRelation::incoming((int) $artist['id']),
            'links'             => ArtistLink::forArtist((int) $artist['id']),
            'videos'            => Video::forArtist((int) $artist['id']),
            'otherArtists'      => array_values(array_filter(
                Artist::all(),
                fn (array $a): bool => (int) $a['id'] !== (int) $artist['id']
            )),
            'canEdit'           => $canEdit,
            'editMode'          => $editMode,
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->render('artists/form', ['errors' => [], 'old' => [], 'mode' => 'create']);
    }

    public function store(): void
    {
        Auth::requireLogin();

        [$data, $errors] = $this->validate();

        if (!empty($errors)) {
            $this->render('artists/form', ['errors' => $errors, 'old' => $data, 'mode' => 'create']);

            return;
        }

        $data['slug'] = $this->uniqueSlug($data['name']);
        $moderationStatus = $this->moderationStatusForCurrentUser();

        Artist::create($data, $data['name'], $data['bio'], (int) Auth::id(), $moderationStatus);

        $this->redirect('/artists/' . $data['slug']);
    }

    /**
     * Étape 1 de l'ajout rapide : on colle une URL de chaîne YouTube.
     */
    public function quickCreateForm(): void
    {
        Auth::requireLogin();
        $this->render('artists/quick_create_url', ['error' => null]);
    }

    /**
     * Étape 2 : on a résolu la chaîne, on vérifie qu'elle n'est pas déjà
     * connue, puis on affiche un mini-formulaire pré-rempli (nom + type).
     */
    public function quickCreatePreview(): void
    {
        Auth::requireLogin();

        $url = trim((string) $this->input('channel_url', ''));
        $channelInfo = YoutubeApiService::fetchChannelInfo($url);

        if ($channelInfo === null) {
            $this->render('artists/quick_create_url', [
                'error' => t('artists.quick_create.bad_url'),
            ]);

            return;
        }

        $existing = ArtistLink::findArtistByYoutubeChannelId($channelInfo['channel_id']);

        if ($existing !== null) {
            $this->render('artists/quick_create_url', [
                'error' => t('artists.quick_create.already_known') . ' ' . $existing['name'],
                'existingArtistSlug' => $existing['slug'],
            ]);

            return;
        }

        $this->render('artists/quick_create_form', [
            'errors'        => [],
            'channelId'     => $channelInfo['channel_id'],
            'canonicalUrl'  => $channelInfo['canonical_url'],
            'suggestedName' => $channelInfo['title'],
            'thumbnailUrl'  => $channelInfo['thumbnail_url'] ?? null,
        ]);
    }

    public function quickCreateStore(): void
    {
        Auth::requireLogin();

        $name = trim((string) $this->input('name', ''));
        $type = (string) $this->input('type', 'solo');
        $channelId = (string) $this->input('channel_id', '');
        $canonicalUrl = (string) $this->input('canonical_url', '');
        $thumbnailUrl = trim((string) $this->input('thumbnail_url', '')) ?: null;

        if (!in_array($type, ['solo', 'group', 'duo', 'other'], true)) {
            $type = 'solo';
        }

        $errors = [];

        if ($name === '') {
            $errors[] = t('artists.error.name_required');
        }

        if ($channelId === '' || $canonicalUrl === '') {
            $errors[] = t('artists.quick_create.missing_channel');
        }

        if (!empty($errors)) {
            $this->render('artists/quick_create_form', [
                'errors'        => $errors,
                'channelId'     => $channelId,
                'canonicalUrl'  => $canonicalUrl,
                'suggestedName' => $name,
                'thumbnailUrl'  => $thumbnailUrl,
            ]);

            return;
        }

        // Re-vérifie qu'elle n'a pas été ajoutée entre-temps (double soumission)
        if (ArtistLink::findArtistByYoutubeChannelId($channelId) !== null) {
            $this->redirect('/artists/quick-create');

            return;
        }

        $slug = $this->uniqueSlug($name);
        $moderationStatus = $this->moderationStatusForCurrentUser();

        $artistId = Artist::create(
            [
                'type'       => $type,
                'status'     => 'active',
                'start_year' => null,
                'end_year'   => null,
                'label'      => null,
                'slug'       => $slug,
            ],
            $name,
            null,
            (int) Auth::id(),
            $moderationStatus
        );

        ArtistLink::create($artistId, 'youtube', $canonicalUrl);

        if ($thumbnailUrl !== null) {
            Artist::updateAvatar($artistId, $thumbnailUrl);
        }

        $this->redirect('/artists/' . $slug);
    }

    public function edit(int $id): void
    {
        Auth::requireLogin();

        $artist = Artist::findById($id);

        if (!$artist || !Auth::canEdit((int) $artist['created_by'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        $translations = Artist::translations($id);
        $fr = $translations['fr'] ?? ['name' => '', 'bio' => ''];

        $old = array_merge($artist, ['name' => $fr['name'], 'bio' => $fr['bio']]);

        $this->render('artists/form', [
            'errors'   => [],
            'old'      => $old,
            'mode'     => 'edit',
            'artistId' => $id,
        ]);
    }

    public function update(int $id): void
    {
        Auth::requireLogin();

        $artist = Artist::findById($id);

        if (!$artist || !Auth::canEdit((int) $artist['created_by'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        [$data, $errors] = $this->validate();

        if (!empty($errors)) {
            $this->render('artists/form', [
                'errors'   => $errors,
                'old'      => $data,
                'mode'     => 'edit',
                'artistId' => $id,
            ]);

            return;
        }

        $data['slug'] = Slug::make($data['name']) === $artist['slug']
            ? $artist['slug']
            : $this->uniqueSlug($data['name'], $id);

        Artist::update($id, $data, $data['name'], $data['bio']);

        $this->redirect('/artists/' . $data['slug']);
    }

    public function delete(int $id): void
    {
        Auth::requireLogin();

        $artist = Artist::findById($id);

        if (!$artist || !Auth::canEdit((int) $artist['created_by'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        Artist::delete($id);
        $this->redirect('/artists');
    }

    public function updateAvatar(int $id): void
    {
        Auth::requireLogin();

        $artist = Artist::findById($id);

        if (!$artist || !Auth::canEdit((int) $artist['created_by'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        $url = trim((string) $this->input('avatar_url', ''));

        if ($url === '') {
            Artist::updateAvatar($id, null);
        } elseif (filter_var($url, FILTER_VALIDATE_URL)) {
            Artist::updateAvatar($id, $url);
        }

        $this->redirect('/artists/' . $artist['slug'] . '?edit=1');
    }

    public function importAvatarFromYoutube(int $id): void
    {
        Auth::requireLogin();

        $artist = Artist::findById($id);

        if (!$artist || !Auth::canEdit((int) $artist['created_by'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        $youtubeUrl = null;
        foreach (ArtistLink::forArtist($id) as $link) {
            if ($link['platform'] === 'youtube') {
                $youtubeUrl = $link['url'];

                break;
            }
        }

        if ($youtubeUrl !== null) {
            $channelInfo = YoutubeApiService::fetchChannelInfo($youtubeUrl);

            if ($channelInfo !== null && !empty($channelInfo['thumbnail_url'])) {
                Artist::updateAvatar($id, $channelInfo['thumbnail_url']);
            }
        }

        $this->redirect('/artists/' . $artist['slug'] . '?edit=1');
    }

    public function addLinksBulk(int $id): void
    {
        Auth::requireLogin();

        $artist = Artist::findById($id);

        if (!$artist || !Auth::canEdit((int) $artist['created_by'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        $raw = (string) $this->input('links_bulk', '');
        ArtistLink::addBulk($id, $raw);

        $this->redirect('/artists/' . $artist['slug'] . '?edit=1');
    }

    public function deleteLink(int $id, int $linkId): void
    {
        Auth::requireLogin();

        $artist = Artist::findById($id);

        if (!$artist || !Auth::canEdit((int) $artist['created_by'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        $link = ArtistLink::findById($linkId);

        if ($link && (int) $link['artist_id'] === $id) {
            ArtistLink::delete($linkId);
        }

        $this->redirect('/artists/' . $artist['slug'] . '?edit=1');
    }

    public function addRelation(int $id): void
    {
        Auth::requireLogin();

        $artist = Artist::findById($id);

        if (!$artist || !Auth::canEdit((int) $artist['created_by'])) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        $relatedId = (int) $this->input('related_artist_id', 0);
        $type = (string) $this->input('relation_type', '');
        $note = trim((string) $this->input('note', '')) ?: null;

        $validTypes = ['member_of', 'former_member_of', 'solo_project_of', 'collaborates_with'];

        if ($relatedId > 0 && $relatedId !== $id && in_array($type, $validTypes, true) && Artist::findById($relatedId)) {
            ArtistRelation::create($id, $relatedId, $type, $note);
        }

        $this->redirect('/artists/' . $artist['slug'] . '?edit=1');
    }

    public function deleteRelation(int $id, int $relationId): void
    {
        Auth::requireLogin();

        $relation = ArtistRelation::findById($relationId);

        if (!$relation) {
            $this->redirect('/artists');

            return;
        }

        $sourceArtist = Artist::findById((int) $relation['artist_id']);
        $targetArtist = Artist::findById((int) $relation['related_artist_id']);

        $canDelete = ($sourceArtist && Auth::canEdit((int) $sourceArtist['created_by']))
            || ($targetArtist && Auth::canEdit((int) $targetArtist['created_by']));

        if (!$canDelete) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';

            return;
        }

        ArtistRelation::delete($relationId);

        $artist = Artist::findById($id);
        $this->redirect($artist ? '/artists/' . $artist['slug'] . '?edit=1' : '/artists');
    }

    /**
     * Un modérateur/admin voit son artiste publié immédiatement ; un
     * utilisateur normal passe systématiquement par la file de validation.
     */
    private function moderationStatusForCurrentUser(): string
    {
        return in_array(Auth::role(), ['moderator', 'admin'], true) ? 'approved' : 'pending';
    }

    /**
     * @return array{0: array, 1: string[]}
     */
    private function validate(): array
    {
        $data = [
            'name'       => trim((string) $this->input('name', '')),
            'bio'        => trim((string) $this->input('bio', '')) ?: null,
            'type'       => (string) $this->input('type', 'solo'),
            'status'     => (string) $this->input('status', 'active'),
            'start_year' => ((int) $this->input('start_year', 0)) ?: null,
            'end_year'   => ((int) $this->input('end_year', 0)) ?: null,
            'label'      => trim((string) $this->input('label', '')) ?: null,
        ];

        $errors = [];

        if ($data['name'] === '') {
            $errors[] = t('artists.error.name_required');
        }

        if (!in_array($data['type'], ['solo', 'group', 'duo', 'other'], true)) {
            $errors[] = t('artists.error.type_invalid');
        }

        if (!in_array($data['status'], ['active', 'disbanded', 'hiatus'], true)) {
            $errors[] = t('artists.error.status_invalid');
        }

        return [$data, $errors];
    }

    private function uniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Slug::make($name);
        $slug = $base;
        $i = 2;

        while (Artist::slugExists($slug, $excludeId)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
