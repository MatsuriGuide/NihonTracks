<?php

namespace App\Controllers\Admin;

use App\Models\Artist;
use App\Models\Video;

class VideoTagBackfillController extends AdminController
{
    /**
     * Nombre de vidéos traitées par clic — reste dans les limites de temps
     * d'exécution PHP même si beaucoup de vidéos sont concernées. Si le
     * compteur restant n'est pas à zéro après un lancement, il suffit de
     * recliquer pour continuer le lot suivant.
     */
    private const BATCH_SIZE = 500;

    public function index(): void
    {
        $this->render('admin/video-tag-backfill/index', [
            'candidateCount' => count(Video::idsWithoutTags()),
            'result'         => null,
        ]);
    }

    public function run(): void
    {
        $videoIds = array_slice(Video::idsWithoutTags(), 0, self::BATCH_SIZE);
        $processed = count($videoIds);
        $updated = 0;

        foreach ($videoIds as $videoId) {
            $artistIds = array_column(Video::artistsFor($videoId), 'id');

            $tagIds = [];
            foreach ($artistIds as $artistId) {
                $tagIds = array_merge($tagIds, Artist::tagIdsFor((int) $artistId));
            }
            $tagIds = array_unique($tagIds);

            if (!empty($tagIds)) {
                Video::addTags($videoId, $tagIds);
                $updated++;
            }
        }

        $this->render('admin/video-tag-backfill/index', [
            'candidateCount' => count(Video::idsWithoutTags()),
            'result'         => [
                'processed' => $processed,
                'updated'   => $updated,
            ],
        ]);
    }
}
