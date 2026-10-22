<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\VideoStats;

class TopController extends Controller
{
    public function index(): void
    {
        $weeks = VideoStats::weeks();
        $requested = (string) $this->input('week', '');

        $weekDate = in_array($requested, $weeks, true) ? $requested : ($weeks[0] ?? null);

        if ($weekDate === null) {
            $this->render('top/index', ['weekDate' => null]);

            return;
        }

        $prevWeek = VideoStats::previousWeek($weekDate);
        $isLatest = $weekDate === $weeks[0];

        $this->render('top/index', [
            'weekDate'     => $weekDate,
            'prevWeek'     => $prevWeek,
            'nextWeek'     => VideoStats::nextWeek($weekDate),
            'top'          => VideoStats::top($weekDate, $prevWeek, 10),
            'bestProgress' => VideoStats::bestProgress($weekDate, $prevWeek, 3),
            // Dernière semaine : les 7 derniers jours jusqu'à aujourd'hui,
            // plus utile qu'une fenêtre figée au jeudi.
            'releases'     => VideoStats::weekReleases($isLatest ? date('Y-m-d') : $weekDate, 12),
        ]);
    }

    /** Seuils d'abonnés proposés pour "artistes émergents". */
    public const SUBSCRIBER_LIMITS = [5000, 10000, 25000];

    public function artists(): void
    {
        $weeks = VideoStats::weeks();
        $requested = (string) $this->input('week', '');
        $weekDate = in_array($requested, $weeks, true) ? $requested : ($weeks[0] ?? null);

        $maxSubscribers = (int) $this->input('max', 10000);
        if (!in_array($maxSubscribers, self::SUBSCRIBER_LIMITS, true)) {
            $maxSubscribers = 10000;
        }

        if ($weekDate === null) {
            $this->render('top/artists', ['weekDate' => null, 'limits' => self::SUBSCRIBER_LIMITS, 'maxSubscribers' => $maxSubscribers]);

            return;
        }

        $prevWeek = VideoStats::previousWeek($weekDate);

        $this->render('top/artists', [
            'weekDate'       => $weekDate,
            'prevWeek'       => $prevWeek,
            'nextWeek'       => VideoStats::nextWeek($weekDate),
            'artists'        => VideoStats::emergingArtists($weekDate, $prevWeek, $maxSubscribers, 10),
            'limits'         => self::SUBSCRIBER_LIMITS,
            'maxSubscribers' => $maxSubscribers,
        ]);
    }

    /**
     * Export CSV d'un classement (même structure que l'export "extraits" de
     * l'admin, plus rang / progression / rang_precedent). Réservé aux
     * modérateurs et admins, comme l'export existant.
     *
     * type : videos (top des clips) | progress (meilleures progressions) |
     *        artists (artistes émergents, une ligne par artiste avec sa
     *        meilleure vidéo de la semaine)
     */
    public function exportCsv(): void
    {
        Auth::requireRole(['moderator', 'admin']);

        $weeks = VideoStats::weeks();
        $requested = (string) $this->input('week', '');
        $weekDate = in_array($requested, $weeks, true) ? $requested : ($weeks[0] ?? null);

        $type = (string) $this->input('type', 'videos');
        if (!in_array($type, ['videos', 'progress', 'artists'], true)) {
            $type = 'videos';
        }

        $limit = max(1, min(50, (int) $this->input('limit', 10)));

        $maxSubscribers = (int) $this->input('max', 10000);
        if (!in_array($maxSubscribers, self::SUBSCRIBER_LIMITS, true)) {
            $maxSubscribers = 10000;
        }

        // Lignes normalisées : artiste, titre, url, rank, prev_rank.
        $items = [];

        if ($weekDate !== null) {
            $prevWeek = VideoStats::previousWeek($weekDate);

            if ($type === 'artists') {
                foreach (VideoStats::emergingArtists($weekDate, $prevWeek, $maxSubscribers, $limit) as $a) {
                    $items[] = [
                        'artist'    => (string) ($a['name'] ?? ''),
                        'title'     => (string) ($a['best_video_title'] ?? ''),
                        'youtube_id' => $a['best_video_youtube_id'] ?? null,
                        'rank'      => (int) $a['rank'],
                        'prev_rank' => $a['prev_rank'],
                    ];
                }
            } else {
                $rows = $type === 'progress'
                    ? VideoStats::bestProgress($weekDate, $prevWeek, $limit)
                    : VideoStats::top($weekDate, $prevWeek, $limit);

                foreach ($rows as $r) {
                    $items[] = [
                        'artist'    => (string) ($r['artist_names'] ?? ''),
                        'title'     => (string) ($r['title'] ?? ''),
                        'youtube_id' => $r['youtube_id'] ?? null,
                        'rank'      => (int) $r['rank_pos'],
                        'prev_rank' => $r['prev_rank'],
                    ];
                }
            }
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="top-' . $type . '-' . ($weekDate ?? 'vide') . '.csv"');

        // BOM UTF-8 : sans lui, Excel affiche mal les accents.
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        fputcsv(
            $output,
            ['ordre', 'artiste', 'artiste_transcription', 'titre', 'titre_transcription', 'traduction', 'url', 'debut', 'fin', 'description', 'rang', 'progression', 'rang_precedent'],
            ';'
        );

        $counterSeconds = 0;
        $order = 1;

        foreach ($items as $item) {
            $counterSeconds += 10;
            $url = !empty($item['youtube_id']) ? 'https://www.youtube.com/watch?v=' . $item['youtube_id'] : '';

            // progression = places gagnées (+) ou perdues (-) ; "new" = nouvelle entrée.
            if ($item['prev_rank'] === null) {
                $progression = 'new';
                $prevRank = '';
            } else {
                $progression = (int) $item['prev_rank'] - $item['rank'];
                $prevRank = (int) $item['prev_rank'];
            }

            fputcsv(
                $output,
                [
                    $order,
                    $item['artist'],
                    '',
                    $item['title'],
                    '',
                    '',
                    $url,
                    '01:00',
                    '01:10',
                    self::formatTimestamp($counterSeconds) . ' ' . $url,
                    $item['rank'],
                    $progression,
                    $prevRank,
                ],
                ';'
            );

            $order++;
        }

        fclose($output);
        exit;
    }

    private static function formatTimestamp(int $totalSeconds): string
    {
        return intdiv($totalSeconds, 60) . ':' . str_pad((string) ($totalSeconds % 60), 2, '0', STR_PAD_LEFT);
    }
}
