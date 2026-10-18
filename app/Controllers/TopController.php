<?php

namespace App\Controllers;

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
}
