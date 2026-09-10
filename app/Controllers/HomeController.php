<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Tag;
use App\Models\Video;

class HomeController extends Controller
{
    public function index(): void
    {
        $latestVideos = Video::latest(12);
        $latestIds = array_map(static fn (array $v): int => (int) $v['id'], $latestVideos);

        // Vidéos "à découvrir" : tirées au hasard, jamais les mêmes que
        // celles déjà montrées dans "Dernières sorties" (voir randomDiscover).
        $discoverVideos = Video::randomDiscover(6, $latestIds);

        // 8 plutôt que 6 : avec la grille compacte (4 colonnes sur desktop),
        // 6 laissait une ligne à moitié vide (4 + 2) ; 8 remplit deux lignes
        // complètes.
        $discoverArtists = Artist::randomWithVideos(8);
        $newArtists = Artist::recentlyAdded(8);

        $tagGroups = Tag::selectable();
        $genreTags = $tagGroups['genre']['tags'] ?? [];

        $publicPlaylists = array_slice(Playlist::allPublic(), 0, 6);
        foreach ($publicPlaylists as &$playlist) {
            $playlist['preview_thumbnails'] = Playlist::previewThumbnails((int) $playlist['id'], 4);
        }
        unset($playlist);

        $this->render('home/index', [
            'latestVideos'    => $latestVideos,
            'discoverVideos'  => $discoverVideos,
            'discoverArtists' => $discoverArtists,
            'newArtists'      => $newArtists,
            'genreTags'       => $genreTags,
            'playlists'       => $publicPlaylists,
            'artistCount'     => Artist::countApproved(),
            'videoCount'      => Video::countPublished(),
            'playlistCount'   => Playlist::countAll(),
        ]);
    }
}
