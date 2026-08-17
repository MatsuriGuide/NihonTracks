<?php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Lang;
use App\Services\ChannelWatcherService;
use App\Services\YoutubeApiService;

class WatchController extends AdminController
{
    public function index(): void
    {
        $lang = Lang::current();

        $links = Database::getInstance()->fetchAll(
            'SELECT al.id AS link_id, al.url, a.slug AS artist_slug,
                    COALESCE(ai.name, ai_fr.name) AS artist_name
             FROM artist_links al
             JOIN artists a ON a.id = al.artist_id
             LEFT JOIN artists_i18n ai ON ai.artist_id = a.id AND ai.lang = ?
             LEFT JOIN artists_i18n ai_fr ON ai_fr.artist_id = a.id AND ai_fr.lang = "fr"
             WHERE al.platform = "youtube"
             ORDER BY artist_name',
            [$lang]
        );

        $channels = array_map(static function (array $row): array {
            $row['channel_id'] = YoutubeApiService::extractChannelId($row['url']);

            return $row;
        }, $links);

        $this->render('admin/watch/index', [
            'channels'   => $channels,
            'scanResult' => $this->input('found'),
        ]);
    }

    public function scanOne(int $linkId): void
    {
        $limit = (int) $this->input('limit', 50);

        if (!in_array($limit, [25, 50, 100], true)) {
            $limit = 50;
        }

        $link = Database::getInstance()->fetchOne(
            'SELECT * FROM artist_links WHERE id = ? AND platform = "youtube"',
            [$linkId]
        );

        $found = 0;

        if ($link) {
            $channelId = YoutubeApiService::extractChannelId($link['url']);

            if ($channelId !== null) {
                $found = ChannelWatcherService::scanArtist((int) $link['artist_id'], $channelId, $limit);
            }
        }

        $this->redirect('/admin/watch?found=' . $found);
    }
}
