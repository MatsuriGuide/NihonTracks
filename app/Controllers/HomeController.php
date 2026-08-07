<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HomeController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();

        $lang = \App\Core\Lang::current();

        // Exemple : dernières vidéos publiées (une fois la table remplie)
        $latestVideos = $db->fetchAll(
            'SELECT v.id, v.youtube_id, v.release_date,
                    COALESCE(vi.title, vi_fr.title) AS title,
                    GROUP_CONCAT(DISTINCT COALESCE(ai.name, ai_fr.name) ORDER BY ai.name SEPARATOR ", ") AS artist_names
             FROM videos v
             LEFT JOIN videos_i18n vi ON vi.video_id = v.id AND vi.lang = ?
             LEFT JOIN videos_i18n vi_fr ON vi_fr.video_id = v.id AND vi_fr.lang = "fr"
             LEFT JOIN video_artists va ON va.video_id = v.id
             LEFT JOIN artists_i18n ai ON ai.artist_id = va.artist_id AND ai.lang = ?
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = va.artist_id AND ai_fr.lang = "fr"
             WHERE v.status = "published"
             GROUP BY v.id
             ORDER BY v.release_date DESC
             LIMIT 12',
            [$lang, $lang]
        );

        $this->render('home/index', [
            'latestVideos' => $latestVideos,
        ]);
    }
}
