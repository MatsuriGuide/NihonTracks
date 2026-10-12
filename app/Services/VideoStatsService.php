<?php

namespace App\Services;

use App\Core\Database;

/**
 * TOP hebdomadaire des clips (type "mv").
 *
 * Principe : chaque jeudi, au premier passage du CRON, on relève les vues des
 * clips "suivis" (les 50 premiers du classement précédent + tous les MV sortis
 * depuis moins de deux mois). Le gain de la semaine = vues du relevé - vues
 * enregistrées précédemment (relevé de la semaine d'avant, ou vues de départ
 * stockées à l'ajout de la vidéo). Le classement se fait sur ce gain.
 *
 * Compatible PHP 7.4 (chargé par les scripts CRON en ligne de commande).
 */
class VideoStatsService
{
    public const TRACKED_TOP_SIZE = 50;
    public const RECENT_MONTHS = 2;
    public const BACKFILL_MONTHS = 1;

    /**
     * Jeudi le plus récent (aujourd'hui inclus), au format Y-m-d.
     */
    public static function currentWeekDate(?int $now = null): string
    {
        $now = $now ?? time();

        if ((int) date('N', $now) === 4) {
            return date('Y-m-d', $now);
        }

        // "last thursday" = jeudi strictement précédent.
        return date('Y-m-d', strtotime('last thursday', $now));
    }

    public static function lastRunDate(): ?string
    {
        $row = Database::getInstance()->fetchOne('SELECT MAX(week_date) AS d FROM video_stats_runs');

        return $row['d'] ?? null;
    }

    public static function isDue(): bool
    {
        $row = Database::getInstance()->fetchOne(
            'SELECT week_date FROM video_stats_runs WHERE week_date = ?',
            [self::currentWeekDate()]
        );

        return $row === null;
    }

    /**
     * Lance le relevé de la semaine s'il n'a pas encore eu lieu. Rattrape
     * aussi un jeudi manqué (le relevé se fait alors le premier passage
     * suivant). Retourne null si rien à faire ou si l'API YouTube n'a rien
     * renvoyé (le relevé sera retenté au prochain passage).
     *
     * @return array{week_date: string, measured: int, ranked: int}|null
     */
    public static function runWeeklyIfDue(): ?array
    {
        if (!self::isDue()) {
            return null;
        }

        return self::runWeekly();
    }

    /**
     * @return array{week_date: string, measured: int, ranked: int}|null
     */
    public static function runWeekly(): ?array
    {
        $db = Database::getInstance();
        $weekDate = self::currentWeekDate();

        $prevWeek = $db->fetchOne(
            'SELECT MAX(week_date) AS d FROM video_stats_runs WHERE week_date < ?',
            [$weekDate]
        )['d'] ?? null;

        $params = [$weekDate];
        $topClause = '';

        if ($prevWeek !== null) {
            $topClause = ' OR v.id IN (SELECT video_id FROM video_weekly_stats WHERE week_date = ? AND rank_pos <= ' . (int) self::TRACKED_TOP_SIZE . ')';
            $params[] = $prevWeek;
        }

        $candidates = $db->fetchAll(
            'SELECT v.id, v.youtube_id, v.view_count
             FROM videos v
             WHERE v.status = "published" AND v.video_type = "mv"
               AND (v.release_date >= DATE_SUB(?, INTERVAL ' . (int) self::RECENT_MONTHS . ' MONTH)' . $topClause . ')',
            $params
        );

        $views = [];
        if (!empty($candidates)) {
            $views = YoutubeApiService::fetchVideosViews(array_column($candidates, 'youtube_id'));

            if (empty($views)) {
                // API indisponible / quota épuisé : on ne marque pas la
                // semaine comme faite, le prochain passage réessaiera.
                return null;
            }
        }

        $rows = [];
        $now = date('Y-m-d H:i:s');

        foreach ($candidates as $c) {
            if (!isset($views[$c['youtube_id']])) {
                continue;
            }

            $current = (int) $views[$c['youtube_id']];

            $rows[] = [
                'id'      => (int) $c['id'],
                'current' => $current,
                // Pas de vues de départ connues : on les enregistre cette
                // semaine, le clip sera classé la semaine suivante.
                'gain'    => $c['view_count'] !== null ? $current - (int) $c['view_count'] : null,
            ];
        }

        $ranked = array_values(array_filter($rows, static function (array $r): bool {
            return $r['gain'] !== null;
        }));

        usort($ranked, static function (array $a, array $b): int {
            if ($a['gain'] !== $b['gain']) {
                return $b['gain'] <=> $a['gain'];
            }

            return $b['current'] <=> $a['current'];
        });

        $pdo = $db->pdo();
        $pdo->beginTransaction();

        try {
            foreach ($ranked as $i => $r) {
                $db->query(
                    'INSERT INTO video_weekly_stats (video_id, week_date, view_count, gain, rank_pos)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE view_count = VALUES(view_count), gain = VALUES(gain), rank_pos = VALUES(rank_pos)',
                    [$r['id'], $weekDate, $r['current'], $r['gain'], min(65535, $i + 1)]
                );
            }

            foreach ($rows as $r) {
                $db->query(
                    'UPDATE videos SET view_count = ?, view_count_at = ? WHERE id = ?',
                    [$r['current'], $now, $r['id']]
                );
            }

            $db->query(
                'INSERT INTO video_stats_runs (week_date, ran_at, measured, ranked) VALUES (?, ?, ?, ?)',
                [$weekDate, $now, count($rows), count($ranked)]
            );

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return ['week_date' => $weekDate, 'measured' => count($rows), 'ranked' => count($ranked)];
    }

    /**
     * Passe d'initialisation : enregistre les vues actuelles des MV de moins
     * d'un mois qui n'ont pas encore de vues de départ, comme si c'était le
     * résultat du jeudi précédent. Ne touche JAMAIS une vidéo qui a déjà un
     * compteur (sinon on fausserait le gain en cours de semaine).
     *
     * @return array{candidates: int, stored: int}
     */
    public static function backfillBaselines(): array
    {
        $db = Database::getInstance();

        $candidates = $db->fetchAll(
            'SELECT id, youtube_id FROM videos
             WHERE status = "published" AND video_type = "mv" AND view_count IS NULL
               AND release_date >= DATE_SUB(CURDATE(), INTERVAL ' . (int) self::BACKFILL_MONTHS . ' MONTH)'
        );

        if (empty($candidates)) {
            return ['candidates' => 0, 'stored' => 0];
        }

        $views = YoutubeApiService::fetchVideosViews(array_column($candidates, 'youtube_id'));
        $now = date('Y-m-d H:i:s');
        $stored = 0;

        foreach ($candidates as $c) {
            if (!isset($views[$c['youtube_id']])) {
                continue;
            }

            $db->query(
                'UPDATE videos SET view_count = ?, view_count_at = ? WHERE id = ? AND view_count IS NULL',
                [(int) $views[$c['youtube_id']], $now, (int) $c['id']]
            );
            $stored++;
        }

        return ['candidates' => count($candidates), 'stored' => $stored];
    }
}
