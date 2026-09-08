<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Video;
use App\Services\AuditExportService;

class ExportController extends AdminController
{
    public function __construct()
    {
        // Données potentiellement sensibles en masse (emails de
        // propriétaires de playlists, etc.) — réservé aux admins,
        // plus restrictif que le reste de l'admin (modo+admin).
        Auth::requireRole('admin');
    }

    public function index(): void
    {
        $this->render('admin/exports/index', [
            'artistCount'   => Artist::countApproved(),
            'videoCount'    => Video::countPublished(),
            'playlistCount' => Playlist::countAll(),
            'generatedAt'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function artistsJson(): void
    {
        [$artists, ] = AuditExportService::artistsData();

        $this->downloadJson([
            'generated_at' => date('c'),
            'count'        => count($artists),
            'artists'      => $artists,
        ], 'nihontracks-artists');
    }

    public function artistsCsv(): void
    {
        [$artists, ] = AuditExportService::artistsData();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="nihontracks-artists.csv"');
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'id', 'code', 'name', 'name_jp', 'slug', 'type', 'status', 'moderation_status',
            'start_year', 'end_year', 'label', 'tags', 'youtube_channels', 'website', 'social_links',
            'video_count', 'relation_count', 'subscriber_count', 'created_at', 'updated_at',
            'missing_bio', 'missing_start_year', 'missing_label', 'missing_tags',
            'missing_youtube_channel', 'has_end_year_but_active', 'inactive_without_end_year', 'possible_duplicate_name',
        ], ';');

        foreach ($artists as $a) {
            fputcsv($output, [
                $a['id'], $a['code'], $a['name'], $a['name_jp'], $a['slug'], $a['type'], $a['status'], $a['moderation_status'],
                $a['start_year'], $a['end_year'], $a['label'],
                implode(', ', $a['tags']),
                implode(', ', array_map(static fn (array $c): string => $c['url'], $a['youtube_channels'])),
                $a['website'],
                implode(', ', array_map(static fn (array $s): string => $s['platform'] . ':' . $s['url'], $a['social_links'])),
                $a['video_count'], $a['relation_count'], $a['subscriber_count'], $a['created_at'], $a['updated_at'],
                $a['quality_flags']['missing_bio'] ? '1' : '0',
                $a['quality_flags']['missing_start_year'] ? '1' : '0',
                $a['quality_flags']['missing_label'] ? '1' : '0',
                $a['quality_flags']['missing_tags'] ? '1' : '0',
                $a['quality_flags']['missing_youtube_channel'] ? '1' : '0',
                $a['quality_flags']['has_end_year_but_active'] ? '1' : '0',
                $a['quality_flags']['inactive_without_end_year'] ? '1' : '0',
                $a['quality_flags']['possible_duplicate_name'] ? '1' : '0',
            ], ';');
        }

        fclose($output);
        exit;
    }

    public function videosJson(): void
    {
        $videos = AuditExportService::videosData();

        $this->downloadJson([
            'generated_at' => date('c'),
            'count'        => count($videos),
            'videos'       => $videos,
        ], 'nihontracks-videos');
    }

    public function videosCsv(): void
    {
        $videos = AuditExportService::videosData();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="nihontracks-videos.csv"');
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'id', 'code', 'youtube_video_id', 'title', 'youtube_url', 'release_date', 'added_at',
            'video_type', 'status', 'source', 'channel_name', 'duration_seconds',
            'artists', 'tags', 'languages', 'voices', 'playlists',
            'missing_artist', 'missing_tags', 'untriaged_type', 'multiple_artists',
        ], ';');

        foreach ($videos as $v) {
            fputcsv($output, [
                $v['id'], $v['code'], $v['youtube_video_id'], $v['title'], $v['youtube_url'], $v['release_date'], $v['added_at'],
                $v['video_type'], $v['status'], $v['source'], $v['channel_name'], $v['duration_seconds'],
                implode(', ', array_map(static fn (array $a): string => $a['name'], $v['artists'])),
                implode(', ', $v['tags']),
                implode(', ', $v['languages']),
                implode(', ', $v['voices']),
                implode(', ', array_map(static fn (array $p): string => $p['name'], $v['playlists'])),
                $v['quality_flags']['missing_artist'] ? '1' : '0',
                $v['quality_flags']['missing_tags'] ? '1' : '0',
                $v['quality_flags']['untriaged_type'] ? '1' : '0',
                $v['quality_flags']['multiple_artists'] ? '1' : '0',
            ], ';');
        }

        fclose($output);
        exit;
    }

    public function taxonomyJson(): void
    {
        $this->downloadJson(AuditExportService::taxonomyData(), 'nihontracks-taxonomy');
    }

    public function playlistsJson(): void
    {
        $playlists = AuditExportService::playlistsData();

        $this->downloadJson([
            'generated_at' => date('c'),
            'count'        => count($playlists),
            'playlists'    => $playlists,
        ], 'nihontracks-playlists');
    }

    public function fullAudit(): void
    {
        $this->downloadJson(AuditExportService::fullAuditData(), 'nihontracks-audit-full');
    }

    private function downloadJson(array $data, string $filenameBase): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filenameBase . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
