<?php

namespace App\Controllers\Admin;

use App\Models\VideoStats;
use App\Services\VideoStatsService;

class TopAdminController extends AdminController
{
    public function index(): void
    {
        $this->renderPage(null);
    }

    /**
     * Passe d'initialisation : vues actuelles des MV de moins d'un mois,
     * stockées comme "résultat du jeudi dernier".
     */
    public function baseline(): void
    {
        $this->renderPage(['type' => 'baseline'] + VideoStatsService::backfillBaselines());
    }

    /**
     * Lance le relevé de la semaine sans attendre le CRON (uniquement si
     * celui de ce jeudi n'a pas déjà eu lieu : le refaire annulerait le gain).
     */
    public function run(): void
    {
        if (!VideoStatsService::isDue()) {
            $this->renderPage(['type' => 'already']);

            return;
        }

        $summary = VideoStatsService::runWeekly();

        $this->renderPage($summary === null ? ['type' => 'failed'] : ['type' => 'run'] + $summary);
    }

    private function renderPage(?array $result): void
    {
        $this->render('admin/top/index', [
            'result'          => $result,
            'weekDate'        => VideoStatsService::currentWeekDate(),
            'lastRun'         => VideoStatsService::lastRunDate(),
            'due'             => VideoStatsService::isDue(),
            'withoutBaseline' => VideoStats::countMvWithoutBaseline(),
        ]);
    }
}
