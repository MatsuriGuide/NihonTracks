<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\VideoFilterPreset;

class NewsletterController extends Controller
{
    /**
     * Désabonnement en un clic depuis le lien présent dans chaque email —
     * volontairement accessible sans connexion (c'est tout l'intérêt d'un
     * tel lien : fonctionner même si la personne n'est pas/plus connectée
     * sur ce navigateur). Désactive la newsletter sur TOUS les filtres de
     * l'utilisateur d'un coup.
     */
    public function unsubscribe(string $token): void
    {
        $user = User::findByUnsubscribeToken($token);

        if ($user === null) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';

            return;
        }

        $userId = (int) $user['id'];

        foreach (VideoFilterPreset::allForUser($userId) as $preset) {
            VideoFilterPreset::setNewsletterEnabled((int) $preset['id'], $userId, false);
        }

        $this->render('newsletter/unsubscribed', [
            'displayName' => $user['display_name'],
        ]);
    }
}
