<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\CsvExport;
use App\Models\Video;

class CsvExportController extends AdminController
{
    public function index(): void
    {
        $lastExport = CsvExport::lastExportedAt();

        $defaultSince = $lastExport !== null
            ? str_replace(' ', 'T', substr($lastExport, 0, 16))
            : date('Y-m-d\TH:i', strtotime('-30 days'));

        $this->render('admin/csv-export/index', [
            'defaultSince' => $defaultSince,
            'lastExport'   => $lastExport,
        ]);
    }

    public function generate(): void
    {
        $rawSince = trim((string) $this->input('since', ''));

        if ($rawSince !== '' && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $rawSince)) {
            $since = str_replace('T', ' ', $rawSince) . ':00';
        } else {
            $since = CsvExport::lastExportedAt() ?? '1970-01-01 00:00:00';
        }

        $videos = Video::officialMvSince($since);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="extraits.csv"');

        // BOM UTF-8 : sans lui, Excel affiche mal les accents français.
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        fputcsv(
            $output,
            ['ordre', 'artiste', 'artiste_transcription', 'titre', 'titre_transcription', 'traduction', 'url', 'debut', 'fin', 'description'],
            ';'
        );

        $counterSeconds = 0;
        $row = 1;

        foreach ($videos as $video) {
            $counterSeconds += 10;
            $timestamp = self::formatTimestamp($counterSeconds);

            fputcsv(
                $output,
                [
                    $row,
                    $video['artist_names'] ?? '',
                    '',
                    $video['title'] ?? '',
                    '',
                    '',
                    $video['youtube_url'],
                    '01:00',
                    '01:10',
                    $timestamp . ' ' . $video['youtube_url'],
                ],
                ';'
            );

            $row++;
        }

        fclose($output);

        CsvExport::record((int) Auth::id());

        exit;
    }

    /**
     * Convertit un nombre de secondes en "M:SS" (minutes sans zéro devant,
     * secondes toujours sur 2 chiffres) — ex. 10 -> "0:10", 70 -> "1:10".
     */
    private static function formatTimestamp(int $totalSeconds): string
    {
        $minutes = intdiv($totalSeconds, 60);
        $seconds = $totalSeconds % 60;

        return $minutes . ':' . str_pad((string) $seconds, 2, '0', STR_PAD_LEFT);
    }
}
