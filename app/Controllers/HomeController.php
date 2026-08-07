<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HomeController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();

        // Exemple : dernières vidéos publiées (une fois la table remplie)
        $latestVideos = $db->fetchAll(
            'SELECT id, youtube_id, release_date FROM videos
             WHERE status = "published"
             ORDER BY release_date DESC
             LIMIT 12'
        );

        $this->render('home/index', [
            'latestVideos' => $latestVideos,
        ]);
    }
}
