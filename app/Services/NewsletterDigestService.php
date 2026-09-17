<?php

namespace App\Services;

use App\Core\Database;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoFilterPreset;

class NewsletterDigestService
{
    /**
     * Parcourt tous les abonnés (au moins un filtre coché "newsletter"),
     * leur envoie un email récapitulatif s'il y a du nouveau depuis leur
     * dernier envoi, et journalise chaque envoi réussi. Retourne un
     * résumé pour affichage/log (nombre de destinataires traités, emails
     * envoyés, erreurs).
     *
     * @return array{checked: int, sent: int, skipped_empty: int, failed: int}
     */
    public static function run(): array
    {
        $subscribers = VideoFilterPreset::allNewsletterSubscribers();

        $checked = 0;
        $sent = 0;
        $skippedEmpty = 0;
        $failed = 0;

        foreach ($subscribers as $userId => $subscriber) {
            $checked++;

            $since = self::lastSentAt($userId) ?? date('Y-m-d H:i:s', strtotime('-1 day'));

            $videos = self::collectNewVideos($subscriber['presets'], $since);

            // Pas de nouveauté aujourd'hui : pas d'email, sur demande
            // explicite (mieux vaut un silence qu'un email vide qui use la
            // patience des abonnés).
            if (empty($videos)) {
                $skippedEmpty++;

                continue;
            }

            $unsubscribeToken = User::getOrCreateUnsubscribeToken($userId);
            [$html, $text, $subject] = self::buildEmail(
                $subscriber['display_name'],
                $videos,
                $unsubscribeToken
            );

            $success = NewsletterMailerService::send(
                $subscriber['email'],
                $subscriber['display_name'],
                $subject,
                $html,
                $text
            );

            if ($success) {
                self::logSend($userId, count($videos));
                $sent++;
            } else {
                $failed++;
            }
        }

        return [
            'checked'       => $checked,
            'sent'          => $sent,
            'skipped_empty' => $skippedEmpty,
            'failed'        => $failed,
        ];
    }

    /**
     * Vidéos nouvelles (depuis $since) correspondant à AU MOINS UN des
     * préréglages de l'utilisateur, dédupliquées — une même vidéo qui
     * matche plusieurs filtres n'apparaît qu'une fois.
     */
    private static function collectNewVideos(array $presets, string $since): array
    {
        $byId = [];

        foreach ($presets as $preset) {
            $artistId = $preset['artist_id'] !== null ? (int) $preset['artist_id'] : null;
            $videoType = $preset['video_type'] ?: null;
            $tagIds = VideoFilterPreset::tagIds($preset);

            $matches = Video::newForPreset($artistId, $tagIds, $videoType, $since);

            foreach ($matches as $video) {
                $byId[(int) $video['id']] = $video;
            }
        }

        // Les plus récentes en premier, cohérent avec le reste du site.
        uasort($byId, static fn (array $a, array $b): int => strcmp($b['release_date'] ?? '', $a['release_date'] ?? ''));

        return array_values($byId);
    }

    private static function lastSentAt(int $userId): ?string
    {
        $row = Database::getInstance()->fetchOne(
            'SELECT sent_at FROM newsletter_sends WHERE user_id = ? ORDER BY sent_at DESC LIMIT 1',
            [$userId]
        );

        return $row['sent_at'] ?? null;
    }

    private static function logSend(int $userId, int $videoCount): void
    {
        Database::getInstance()->query(
            'INSERT INTO newsletter_sends (user_id, sent_at, video_count) VALUES (?, NOW(), ?)',
            [$userId, $videoCount]
        );
    }

    /**
     * @return array{0: string, 1: string, 2: string} [html, texte, sujet]
     */
    private static function buildEmail(string $displayName, array $videos, string $unsubscribeToken): array
    {
        $count = count($videos);
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://nihontracks.koshiki.art', '/');
        $unsubscribeUrl = $baseUrl . '/newsletter/unsubscribe/' . $unsubscribeToken;

        $subject = $count > 1
            ? sprintf('%d nouvelles vidéos sur NihonTracks', $count)
            : 'Une nouvelle vidéo sur NihonTracks';

        $htmlItems = '';
        $textItems = '';

        foreach ($videos as $video) {
            $title = htmlspecialchars($video['title'] ?? $video['youtube_id'], ENT_QUOTES, 'UTF-8');
            $artists = htmlspecialchars($video['artist_names'] ?? '', ENT_QUOTES, 'UTF-8');
            $link = $baseUrl . '/videos/' . (int) $video['id'];

            $htmlItems .= '<tr><td style="padding:10px 0;border-bottom:1px solid #2a2c4a;">'
                . '<a href="' . $link . '" style="color:#38d6b4;text-decoration:none;font-weight:600;">' . $title . '</a><br>'
                . '<span style="color:#9a9cc0;font-size:13px;">' . $artists . '</span>'
                . '</td></tr>';

            $textItems .= '- ' . ($video['title'] ?? $video['youtube_id'])
                . ($video['artist_names'] ? ' (' . $video['artist_names'] . ')' : '')
                . "\n  " . $link . "\n\n";
        }

        $safeName = htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8');

        $html = '<div style="font-family:sans-serif;max-width:560px;margin:0 auto;color:#e5e6f5;background:#10122a;padding:24px;">'
            . '<h1 style="color:#ff6a72;font-size:20px;">NihonTracks</h1>'
            . '<p>Bonjour ' . $safeName . ',</p>'
            . '<p>Voici ce qui correspond à tes filtres depuis ton dernier email :</p>'
            . '<table style="width:100%;border-collapse:collapse;">' . $htmlItems . '</table>'
            . '<p style="margin-top:24px;font-size:12px;color:#6b6d90;">'
            . 'Tu reçois cet email car tu as activé au moins un filtre newsletter sur NihonTracks. '
            . '<a href="' . $unsubscribeUrl . '" style="color:#6b6d90;">Se désabonner de la newsletter</a>.'
            . '</p>'
            . '</div>';

        $text = "Bonjour {$displayName},\n\n"
            . "Voici ce qui correspond à tes filtres depuis ton dernier email :\n\n"
            . $textItems
            . "---\nTu reçois cet email car tu as activé au moins un filtre newsletter sur NihonTracks.\n"
            . "Se désabonner : {$unsubscribeUrl}\n";

        return [$html, $text, $subject];
    }
}
