<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\Artist;
use App\Models\Video;

class TranslationReviewController extends AdminController
{
    private const PER_PAGE = 24;

    public function __construct()
    {
        // Cohérent avec TranslationController : la traduction (et donc sa
        // file d'attente) reste réservée aux admins, pas aux modérateurs.
        Auth::requireRole('admin');
    }

    public function index(): void
    {
        $total = Artist::countMissingTranslations();
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        $page = max(1, (int) $this->input('page', 1));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * self::PER_PAGE;

        $artists = Artist::missingTranslations(self::PER_PAGE, $offset);

        // Les vidéos de chaque artiste servent de contexte ("liste des
        // morceaux") pour juger de ce qu'il y a à traduire — le nombre
        // d'artistes par page reste borné (24 max), donc un aller par
        // artiste ici n'est pas un problème de performance.
        foreach ($artists as &$artist) {
            $artist['videos'] = Video::forArtist((int) $artist['id']);
        }
        unset($artist);

        $this->render('admin/translation-review/index', [
            'artists'    => $artists,
            'page'       => $page,
            'totalPages' => $totalPages,
        ]);
    }
}
