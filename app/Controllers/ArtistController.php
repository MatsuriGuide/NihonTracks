<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Slug;
use App\Models\Artist;

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

        $translations = Artist::translations((int) $artist['id']);
        $lang = \App\Core\Lang::current();

        $this->render('artists/show', [
            'artist'      => $artist,
            'translation' => $translations[$lang] ?? $translations['fr'] ?? null,
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

        Artist::create($data, $data['name'], $data['bio'], (int) Auth::id());

        $this->redirect('/artists/' . $data['slug']);
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
