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
}
