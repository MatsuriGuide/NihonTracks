<?php

namespace App\Services;

use App\Core\Database;

/**
 * Assemble les données d'export destinées à l'audit qualité (artistes,
 * vidéos, playlists, taxonomie). Chaque méthode fait un petit nombre de
 * requêtes en masse (pas une par ligne) puis assemble le résultat en PHP,
 * pour rester raisonnable même avec plusieurs milliers de vidéos.
 */
class AuditExportService
{
    public static function taxonomyData(): array
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT t.id, t.slug, tc.slug AS category_slug, ti_fr.name
             FROM tags t
             JOIN tag_categories tc ON tc.id = t.category_id
             LEFT JOIN tags_i18n ti_fr ON ti_fr.tag_id = t.id AND ti_fr.lang = "fr"
             ORDER BY tc.slug, ti_fr.name'
        );

        $taxonomy = [];
        foreach ($rows as $row) {
            $taxonomy[$row['category_slug']][] = [
                'id'   => (int) $row['id'],
                'name' => $row['name'],
                'slug' => $row['slug'],
            ];
        }

        return $taxonomy;
    }

    /**
     * @return array{0: array, 1: array} [artistes, relations]
     */
    public static function artistsData(): array
    {
        $db = Database::getInstance();

        $artists = $db->fetchAll(
            'SELECT a.id, a.type, a.status, a.moderation_status, a.start_year, a.end_year, a.label,
                    a.subscriber_count, a.slug, a.created_at, a.updated_at,
                    ai_fr.name AS name_fr, ai_fr.bio AS bio_fr,
                    ai_ja.name AS name_ja,
                    (SELECT COUNT(*) FROM video_artists va WHERE va.artist_id = a.id) AS video_count,
                    (SELECT COUNT(*) FROM artist_relations ar WHERE ar.artist_id = a.id OR ar.related_artist_id = a.id) AS relation_count
             FROM artists a
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = a.id AND ai_fr.lang = "fr"
             LEFT JOIN artists_i18n ai_ja ON ai_ja.artist_id = a.id AND ai_ja.lang = "ja"
             ORDER BY a.id'
        );

        // Liens et tags récupérés en une seule requête chacun (pas une par
        // artiste), puis regroupés en PHP — évite le N+1 sur un catalogue
        // qui peut compter plusieurs centaines de fiches.
        $linksByArtist = [];
        foreach ($db->fetchAll('SELECT artist_id, platform, url FROM artist_links') as $link) {
            $linksByArtist[(int) $link['artist_id']][] = $link;
        }

        $tagsByArtist = [];
        $tagRows = $db->fetchAll(
            'SELECT at.artist_id, ti_fr.name
             FROM artist_tags at
             JOIN tags t ON t.id = at.tag_id
             LEFT JOIN tags_i18n ti_fr ON ti_fr.tag_id = t.id AND ti_fr.lang = "fr"'
        );
        foreach ($tagRows as $row) {
            if (!empty($row['name'])) {
                $tagsByArtist[(int) $row['artist_id']][] = $row['name'];
            }
        }

        // Doublons exacts de nom (insensible à la casse) — heuristique
        // simple, ne détecte pas les variantes/fautes de frappe proches.
        $nameCounts = [];
        foreach ($artists as $artist) {
            $key = mb_strtolower(trim((string) $artist['name_fr']));
            if ($key !== '') {
                $nameCounts[$key] = ($nameCounts[$key] ?? 0) + 1;
            }
        }

        $result = [];
        foreach ($artists as $artist) {
            $id = (int) $artist['id'];
            $links = $linksByArtist[$id] ?? [];
            $tags = $tagsByArtist[$id] ?? [];

            $youtubeChannels = array_values(array_filter($links, static fn (array $l): bool => $l['platform'] === 'youtube'));

            $website = null;
            foreach ($links as $l) {
                if ($l['platform'] === 'website') {
                    $website = $l['url'];

                    break;
                }
            }

            $socialLinks = array_values(array_filter(
                $links,
                static fn (array $l): bool => !in_array($l['platform'], ['youtube', 'website'], true)
            ));

            $nameKey = mb_strtolower(trim((string) $artist['name_fr']));

            $result[] = [
                'id'                => $id,
                'code'              => catalog_no('a', $id),
                'name'              => $artist['name_fr'],
                'name_jp'           => $artist['name_ja'],
                // Non stocké séparément dans ce schéma (pas de champ
                // romaji dédié) — laissé à null plutôt qu'inventé.
                'romaji'            => null,
                'slug'              => $artist['slug'],
                'type'              => $artist['type'],
                'status'            => $artist['status'],
                'moderation_status' => $artist['moderation_status'],
                'start_year'        => $artist['start_year'] !== null ? (int) $artist['start_year'] : null,
                'end_year'          => $artist['end_year'] !== null ? (int) $artist['end_year'] : null,
                'label'             => $artist['label'],
                'bio'               => $artist['bio_fr'],
                'tags'              => $tags,
                'youtube_channels'  => array_map(static fn (array $l): array => [
                    'url'        => $l['url'],
                    'channel_id' => YoutubeApiService::extractChannelId($l['url']),
                ], $youtubeChannels),
                'website'           => $website,
                'social_links'      => array_map(static fn (array $l): array => [
                    'platform' => $l['platform'],
                    'url'      => $l['url'],
                ], $socialLinks),
                'video_count'       => (int) $artist['video_count'],
                'relation_count'    => (int) $artist['relation_count'],
                'subscriber_count'  => $artist['subscriber_count'] !== null ? (int) $artist['subscriber_count'] : null,
                'created_at'        => $artist['created_at'],
                'updated_at'        => $artist['updated_at'],
                'quality_flags'     => [
                    'missing_bio'               => empty($artist['bio_fr']),
                    'missing_start_year'        => $artist['start_year'] === null,
                    'missing_label'             => empty($artist['label']),
                    'missing_tags'              => empty($tags),
                    'missing_youtube_channel'   => empty($youtubeChannels),
                    'has_end_year_but_active'   => $artist['status'] === 'active' && $artist['end_year'] !== null,
                    'inactive_without_end_year' => $artist['status'] === 'disbanded' && $artist['end_year'] === null,
                    'possible_duplicate_name'   => ($nameCounts[$nameKey] ?? 0) > 1,
                ],
            ];
        }

        $relationRows = $db->fetchAll(
            'SELECT ar.artist_id AS source_artist_id, sai.name AS source_artist_name,
                    ar.relation_type, ar.related_artist_id AS target_artist_id, tai.name AS target_artist_name
             FROM artist_relations ar
             LEFT JOIN artists_i18n sai ON sai.artist_id = ar.artist_id AND sai.lang = "fr"
             LEFT JOIN artists_i18n tai ON tai.artist_id = ar.related_artist_id AND tai.lang = "fr"
             ORDER BY ar.artist_id'
        );

        $relations = array_map(static fn (array $r): array => [
            'source_artist_id'   => (int) $r['source_artist_id'],
            'source_artist_name' => $r['source_artist_name'],
            'relation_type'      => $r['relation_type'],
            'target_artist_id'   => (int) $r['target_artist_id'],
            'target_artist_name' => $r['target_artist_name'],
        ], $relationRows);

        return [$result, $relations];
    }

    public static function videosData(): array
    {
        $db = Database::getInstance();

        $videos = $db->fetchAll(
            'SELECT v.id, v.youtube_id, v.youtube_url, v.release_date, v.video_type, v.channel_name,
                    v.duration_seconds, v.status, v.source, v.created_at, v.updated_at,
                    vi_fr.title AS title
             FROM videos v
             LEFT JOIN videos_i18n vi_fr ON vi_fr.video_id = v.id AND vi_fr.lang = "fr"
             ORDER BY v.id'
        );

        $artistsByVideo = [];
        $artistRows = $db->fetchAll(
            'SELECT va.video_id, va.artist_id, va.role, ai_fr.name
             FROM video_artists va
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = va.artist_id AND ai_fr.lang = "fr"'
        );
        foreach ($artistRows as $row) {
            $artistsByVideo[(int) $row['video_id']][] = $row;
        }

        $tagsByVideo = [];
        $tagRows = $db->fetchAll(
            'SELECT vt.video_id, ti_fr.name, tc.slug AS category_slug
             FROM video_tags vt
             JOIN tags t ON t.id = vt.tag_id
             JOIN tag_categories tc ON tc.id = t.category_id
             LEFT JOIN tags_i18n ti_fr ON ti_fr.tag_id = t.id AND ti_fr.lang = "fr"'
        );
        foreach ($tagRows as $row) {
            if (!empty($row['name'])) {
                $tagsByVideo[(int) $row['video_id']][] = $row;
            }
        }

        $playlistsByVideo = [];
        $playlistRows = $db->fetchAll(
            'SELECT pv.video_id, p.id AS playlist_id, p.name
             FROM playlist_videos pv
             JOIN playlists p ON p.id = pv.playlist_id'
        );
        foreach ($playlistRows as $row) {
            $playlistsByVideo[(int) $row['video_id']][] = [
                'id'   => (int) $row['playlist_id'],
                'name' => $row['name'],
            ];
        }

        $result = [];
        foreach ($videos as $video) {
            $id = (int) $video['id'];
            $artists = $artistsByVideo[$id] ?? [];
            $tagRowsForVideo = $tagsByVideo[$id] ?? [];
            $tags = array_map(static fn (array $t): string => $t['name'], $tagRowsForVideo);
            $voices = array_values(array_map(
                static fn (array $t): string => $t['name'],
                array_filter($tagRowsForVideo, static fn (array $t): bool => $t['category_slug'] === 'vocalist')
            ));
            $languages = array_values(array_map(
                static fn (array $t): string => $t['name'],
                array_filter($tagRowsForVideo, static fn (array $t): bool => $t['category_slug'] === 'language')
            ));

            $mainArtists = array_values(array_filter($artists, static fn (array $a): bool => $a['role'] === 'main'));

            $result[] = [
                'id'               => $id,
                'code'             => catalog_no('v', $id),
                'youtube_video_id' => $video['youtube_id'],
                'title'            => $video['title'],
                'youtube_url'      => $video['youtube_url'],
                'release_date'     => $video['release_date'],
                'added_at'         => $video['created_at'],
                'updated_at'       => $video['updated_at'],
                'video_type'       => $video['video_type'],
                'status'           => $video['status'],
                'source'           => $video['source'],
                'channel_name'     => $video['channel_name'],
                'duration_seconds' => $video['duration_seconds'] !== null ? (int) $video['duration_seconds'] : null,
                'artists'          => array_map(static fn (array $a): array => [
                    'id'   => (int) $a['artist_id'],
                    'name' => $a['name'],
                    'role' => $a['role'],
                ], $artists),
                'main_artists'     => array_map(static fn (array $a): array => [
                    'id'   => (int) $a['artist_id'],
                    'name' => $a['name'],
                ], $mainArtists),
                'tags'             => $tags,
                'languages'        => $languages,
                'voices'           => $voices,
                'playlists'        => $playlistsByVideo[$id] ?? [],
                'quality_flags'    => [
                    'missing_artist'   => empty($artists),
                    'missing_tags'     => empty($tags),
                    // Notre schéma impose une valeur par défaut ("mv") au
                    // champ video_type, donc il n'est jamais NULL — ce
                    // drapeau repère plutôt les vidéos encore au type
                    // générique "Autre" (celui utilisé par le scan
                    // automatique avant relecture), plus utile en pratique
                    // qu'une simple vérification de valeur nulle.
                    'untriaged_type'   => $video['video_type'] === 'other',
                    'multiple_artists' => count($artists) > 1,
                ],
            ];
        }

        return $result;
    }

    public static function playlistsData(): array
    {
        $db = Database::getInstance();

        $playlists = $db->fetchAll(
            'SELECT p.id, p.name, p.description, p.is_public, p.created_at, p.updated_at,
                    u.display_name AS owner_name
             FROM playlists p
             JOIN users u ON u.id = p.user_id
             ORDER BY p.id'
        );

        $videosByPlaylist = [];
        $rows = $db->fetchAll(
            'SELECT pv.playlist_id, pv.position, v.id AS video_id,
                    vi_fr.title AS title,
                    GROUP_CONCAT(DISTINCT ai_fr.name ORDER BY ai_fr.name SEPARATOR ", ") AS artist_names
             FROM playlist_videos pv
             JOIN videos v ON v.id = pv.video_id
             LEFT JOIN videos_i18n vi_fr ON vi_fr.video_id = v.id AND vi_fr.lang = "fr"
             LEFT JOIN video_artists va ON va.video_id = v.id
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = va.artist_id AND ai_fr.lang = "fr"
             GROUP BY pv.playlist_id, pv.position, v.id, vi_fr.title
             ORDER BY pv.playlist_id, pv.position'
        );
        foreach ($rows as $row) {
            $videosByPlaylist[(int) $row['playlist_id']][] = [
                'id'       => (int) $row['video_id'],
                'title'    => $row['title'],
                'artists'  => $row['artist_names'],
                'position' => (int) $row['position'],
            ];
        }

        $result = [];
        foreach ($playlists as $playlist) {
            $id = (int) $playlist['id'];
            $videos = $videosByPlaylist[$id] ?? [];

            $result[] = [
                'id'          => $id,
                'name'        => $playlist['name'],
                'description' => $playlist['description'],
                'visibility'  => $playlist['is_public'] ? 'public' : 'private',
                'owner'       => $playlist['owner_name'],
                'video_count' => count($videos),
                'videos'      => $videos,
                'created_at'  => $playlist['created_at'],
                'updated_at'  => $playlist['updated_at'],
            ];
        }

        return $result;
    }

    public static function fullAuditData(): array
    {
        [$artists, $relations] = self::artistsData();
        $videos = self::videosData();
        $playlists = self::playlistsData();
        $taxonomy = self::taxonomyData();

        return [
            'generated_at'     => date('c'),
            'stats'            => [
                'artists'   => count($artists),
                'videos'    => count($videos),
                'playlists' => count($playlists),
            ],
            'taxonomy'         => $taxonomy,
            'artists'          => $artists,
            'artist_relations' => $relations,
            'videos'           => $videos,
            'playlists'        => $playlists,
        ];
    }
}
