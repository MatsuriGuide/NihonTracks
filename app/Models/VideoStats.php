<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Lang;

/**
 * Lecture du TOP hebdomadaire (écriture : voir App\Services\VideoStatsService).
 */
class VideoStats
{
    /**
     * Semaines affichables (au moins un clip classé) : le tout premier relevé
     * ne fait qu'enregistrer des vues de départ et n'a donc pas de classement.
     *
     * @return string[] de la plus récente à la plus ancienne
     */
    public static function weeks(): array
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT week_date FROM video_stats_runs WHERE ranked > 0 ORDER BY week_date DESC LIMIT 104'
        );

        return array_map(static function (array $r): string {
            return $r['week_date'];
        }, $rows);
    }

    public static function previousWeek(string $weekDate): ?string
    {
        return Database::getInstance()->fetchOne(
            'SELECT MAX(week_date) AS d FROM video_stats_runs WHERE ranked > 0 AND week_date < ?',
            [$weekDate]
        )['d'] ?? null;
    }

    public static function nextWeek(string $weekDate): ?string
    {
        return Database::getInstance()->fetchOne(
            'SELECT MIN(week_date) AS d FROM video_stats_runs WHERE ranked > 0 AND week_date > ?',
            [$weekDate]
        )['d'] ?? null;
    }

    private const SELECT_BASE = 'SELECT s.rank_pos, s.gain, s.view_count AS views, prev.rank_pos AS prev_rank,
                v.id, v.youtube_id, v.thumbnail_url, v.release_date,
                COALESCE(vi.title, vi_fr.title) AS title,
                GROUP_CONCAT(DISTINCT COALESCE(ai.name, ai_fr.name) ORDER BY ai.name SEPARATOR ", ") AS artist_names
         FROM video_weekly_stats s
         JOIN videos v ON v.id = s.video_id AND v.status = "published"
         LEFT JOIN video_weekly_stats prev ON prev.video_id = s.video_id AND prev.week_date = ?
         LEFT JOIN videos_i18n vi ON vi.video_id = v.id AND vi.lang = ?
         LEFT JOIN videos_i18n vi_fr ON vi_fr.video_id = v.id AND vi_fr.lang = "fr"
         LEFT JOIN video_artists va ON va.video_id = v.id
         LEFT JOIN artists_i18n ai ON ai.artist_id = va.artist_id AND ai.lang = ?
         LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = va.artist_id AND ai_fr.lang = "fr"';

    /**
     * Les $limit premiers de la semaine. prev_rank = rang de la semaine
     * précédente (null = nouvelle entrée).
     */
    public static function top(string $weekDate, ?string $prevWeek, int $limit = 10, ?string $lang = null): array
    {
        $lang = $lang ?? Lang::current();
        $limit = max(1, min(50, $limit));

        return Database::getInstance()->fetchAll(
            self::SELECT_BASE . '
             WHERE s.week_date = ?
             GROUP BY s.id
             ORDER BY s.rank_pos ASC
             LIMIT ' . $limit,
            [$prevWeek ?? '1000-01-01', $lang, $lang, $weekDate]
        );
    }

    /**
     * Plus fortes remontées au classement (places gagnées par rapport à la
     * semaine précédente), parmi les clips classés les deux semaines.
     */
    public static function bestProgress(string $weekDate, ?string $prevWeek, int $limit = 3, ?string $lang = null): array
    {
        if ($prevWeek === null) {
            return [];
        }

        $lang = $lang ?? Lang::current();
        $limit = max(1, min(20, $limit));

        return Database::getInstance()->fetchAll(
            self::SELECT_BASE . '
             WHERE s.week_date = ? AND prev.rank_pos IS NOT NULL AND prev.rank_pos > s.rank_pos
             GROUP BY s.id
             ORDER BY (prev.rank_pos - s.rank_pos) DESC, s.gain DESC
             LIMIT ' . $limit,
            [$prevWeek, $lang, $lang, $weekDate]
        );
    }

    /**
     * MV sortis pendant les 7 jours qui précèdent $until (inclus), avec
     * leurs dernières vues connues.
     */
    public static function weekReleases(string $until, int $limit = 12, ?string $lang = null): array
    {
        $lang = $lang ?? Lang::current();
        $limit = max(1, min(50, $limit));
        $from = date('Y-m-d', strtotime($until . ' -6 days'));

        return Database::getInstance()->fetchAll(
            'SELECT v.id, v.youtube_id, v.thumbnail_url, v.release_date, v.view_count AS views,
                    COALESCE(vi.title, vi_fr.title) AS title,
                    GROUP_CONCAT(DISTINCT COALESCE(ai.name, ai_fr.name) ORDER BY ai.name SEPARATOR ", ") AS artist_names
             FROM videos v
             LEFT JOIN videos_i18n vi ON vi.video_id = v.id AND vi.lang = ?
             LEFT JOIN videos_i18n vi_fr ON vi_fr.video_id = v.id AND vi_fr.lang = "fr"
             LEFT JOIN video_artists va ON va.video_id = v.id
             LEFT JOIN artists_i18n ai ON ai.artist_id = va.artist_id AND ai.lang = ?
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = va.artist_id AND ai_fr.lang = "fr"
             WHERE v.status = "published" AND v.video_type = "mv"
               AND v.release_date >= ? AND v.release_date <= ?
             GROUP BY v.id
             ORDER BY v.release_date DESC, v.view_count DESC
             LIMIT ' . $limit,
            [$lang, $lang, $from, $until]
        );
    }

    /** Vidéos MV publiées sans vues de départ (pour l'écran admin). */
    public static function countMvWithoutBaseline(): int
    {
        return (int) (Database::getInstance()->fetchOne(
            'SELECT COUNT(*) AS n FROM videos
             WHERE status = "published" AND video_type = "mv" AND view_count IS NULL
               AND release_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)'
        )['n'] ?? 0);
    }
}
