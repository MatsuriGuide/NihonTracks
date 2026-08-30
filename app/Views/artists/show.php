<h1><span class="catalog-no"><?= e(catalog_no('a', (int) $artist['id'])) ?></span><?= e($translation['name'] ?? $artist['slug']) ?></h1>

<?php if (!empty($artist['avatar_path'])): ?>
    <img src="<?= e($artist['avatar_path']) ?>" alt="" style="max-width: 200px; border-radius: 3px; display: block; margin-bottom: 1rem;">
<?php endif; ?>

<?php if (!empty($artist['subscriber_count'])): ?>
    <p class="card-meta"><?= e(number_format((int) $artist['subscriber_count'], 0, ',', ' ')) ?> <?= e(t('artists.subscriber_count')) ?></p>
<?php endif; ?>

<?php if ($artist['moderation_status'] === 'pending'): ?>
    <p class="hint"><?= e(t('artists.moderation.pending_banner')) ?></p>
<?php elseif ($artist['moderation_status'] === 'rejected'): ?>
    <p class="hint"><?= e(t('artists.moderation.rejected_banner')) ?></p>
<?php endif; ?>

<p>
    <?= e(t('artists.type')) ?> : <?= e(t('artists.type.' . $artist['type'])) ?> —
    <?= e(t('artists.status')) ?> : <?= e(t('artists.status.' . $artist['status'])) ?>
    <?php if (!empty($artist['start_year'])): ?>
        — <?= e(t('artists.since')) ?> <?= e((string) $artist['start_year']) ?><?= !empty($artist['end_year']) ? ' ' . e(t('artists.until')) . ' ' . e((string) $artist['end_year']) : '' ?>
    <?php endif; ?>
</p>

<?php if (!empty($artist['label'])): ?>
    <p><?= e(t('artists.label')) ?> : <?= e($artist['label']) ?></p>
<?php endif; ?>

<?php if (!empty($translation['bio'])): ?>
    <p><?= nl2br(e($translation['bio'])) ?></p>
<?php endif; ?>

<?php if (!empty($videos)): ?>
    <h2><?= e(t('artists.videos_title')) ?></h2>
    <ul class="card-grid">
        <?php foreach ($videos as $video): ?>
            <li class="card">
                <a href="<?= url('/videos/' . $video['id']) ?>">
                    <?php if (!empty($video['thumbnail_url'])): ?>
                        <div class="card-thumb" style="background-image:url('<?= e($video['thumbnail_url']) ?>');"></div>
                    <?php else: ?>
                        <div class="card-thumb"></div>
                    <?php endif; ?>
                </a>
                <div class="card-body">
                    <a href="<?= url('/videos/' . $video['id']) ?>" class="card-title">
                        <?= e($video['title'] ?? $video['youtube_id']) ?>
                    </a>
                    <span class="card-meta">
                        <?= e(t('videos.type.' . $video['video_type'])) ?>
                        <?php if (!empty($video['release_date'])): ?>
                            — <?= e($video['release_date']) ?>
                        <?php endif; ?>
                    </span>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($canEdit): ?>
    <p>
        <?php if ($editMode): ?>
            <a href="<?= url('/artists/' . $artist['slug']) ?>" class="btn btn-small"><?= e(t('artists.view_mode')) ?></a>
        <?php else: ?>
            <a href="<?= url('/artists/' . $artist['slug'] . '?edit=1') ?>" class="btn btn-small"><?= e(t('artists.edit_mode')) ?></a>
        <?php endif; ?>
    </p>
<?php endif; ?>

<?php if ($editMode): ?>
    <p>
        <a href="<?= url('/artists/' . $artist['id'] . '/edit') ?>"><?= e(t('artists.edit')) ?></a>
        &nbsp;
        <form method="post" action="<?= url('/artists/' . $artist['id'] . '/delete') ?>"
              onsubmit="return confirm('<?= e(t('artists.delete_confirm')) ?>');" style="display:inline">
            <button type="submit"><?= e(t('artists.delete')) ?></button>
        </form>
    </p>

    <details>
        <summary><?= e(t('artists.avatar_label')) ?></summary>
        <form method="post" action="<?= url('/artists/' . $artist['id'] . '/avatar') ?>">
            <p>
                <input type="url" name="avatar_url" placeholder="https://..."
                       value="<?= e($artist['avatar_path'] ?? '') ?>" style="width: 100%; max-width: 500px;">
            </p>
            <p><small><?= e(t('artists.avatar_hint')) ?></small></p>
            <button type="submit"><?= e(t('artists.avatar_save')) ?></button>
        </form>
        <?php
        $hasYoutubeLink = false;
        foreach ($links as $link) {
            if ($link['platform'] === 'youtube') {
                $hasYoutubeLink = true;
                break;
            }
        }
        ?>
        <?php if ($hasYoutubeLink): ?>
            <form method="post" action="<?= url('/artists/' . $artist['id'] . '/avatar/import-youtube') ?>" style="margin-top: 0.5rem;">
                <button type="submit"><?= e(t('artists.avatar_import_youtube')) ?></button>
            </form>
        <?php endif; ?>
    </details>

    <?php if (\App\Core\Auth::role() === 'admin'): ?>
        <div class="admin-translate">
            <h3><?= e(t('admin.translate.title')) ?></h3>
            <?php foreach (['en', 'ja'] as $targetLang): ?>
                <?php $existingTranslation = $allTranslations[$targetLang] ?? null; ?>
                <form method="post" action="<?= url('/admin/translate/artist/' . $artist['id'] . '/' . $targetLang) ?>" style="display:inline">
                    <button type="submit">
                        <?= $existingTranslation ? e(t('admin.translate.retranslate')) : e(t('admin.translate.translate')) ?>
                        (<?= e(strtoupper($targetLang)) ?>)
                    </button>
                </form>
                <?php if (!empty($existingTranslation['is_auto_translated'])): ?>
                    <small><?= e(t('admin.translate.auto_flag')) ?></small>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<h2><?= e(t('artists.tags_label')) ?></h2>

<?php if (empty($artistTags)): ?>
    <p><?= e(t('artists.tags_none')) ?></p>
<?php else: ?>
    <p>
        <?php foreach ($artistTags as $tag): ?>
            <span class="tag"><?= e($tag['name'] ?? '') ?></span>
        <?php endforeach; ?>
    </p>
<?php endif; ?>

<?php if ($editMode): ?>
    <details>
        <summary><?= e(t('artists.tags_edit')) ?></summary>
        <form method="post" action="<?= url('/artists/' . $artist['id'] . '/tags') ?>">
            <?php foreach ($tagGroups as $categorySlug => $tags): ?>
                <fieldset>
                    <legend><?= e(ucfirst($categorySlug)) ?></legend>
                    <?php foreach ($tags as $tag): ?>
                        <label>
                            <input type="checkbox" name="tag_ids[]" value="<?= (int) $tag['id'] ?>"
                                <?= in_array((int) $tag['id'], $artistTagIds, true) ? 'checked' : '' ?>>
                            <?= e($tag['name'] ?? $tag['slug']) ?>
                        </label><br>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
            <p><small><?= e(t('artists.tags_hint')) ?></small></p>
            <button type="submit"><?= e(t('artists.tags_save')) ?></button>
        </form>
    </details>
<?php endif; ?>

<h2><?= e(t('artists.links.title')) ?></h2>

<?php if (empty($links)): ?>
    <p><?= e(t('artists.links.none')) ?></p>
<?php else: ?>
    <ul>
        <?php foreach ($links as $link): ?>
            <li>
                <strong><?= e(t('artists.links.platform.' . $link['platform'])) ?></strong> :
                <a href="<?= e($link['url']) ?>" target="_blank" rel="noopener"><?= e($link['url']) ?></a>
                <?php if ($editMode): ?>
                    <form method="post" action="<?= url('/artists/' . $artist['id'] . '/links/' . $link['id'] . '/delete') ?>"
                          onsubmit="return confirm('<?= e(t('artists.links.remove_confirm')) ?>');" style="display:inline">
                        <button type="submit"><?= e(t('artists.links.remove')) ?></button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($editMode): ?>
    <details open>
        <summary><?= e(t('artists.links.add_title')) ?></summary>
        <form method="post" action="<?= url('/artists/' . $artist['id'] . '/links/bulk') ?>">
            <p>
                <label for="links_bulk"><?= e(t('artists.links.bulk_label')) ?></label><br>
                <textarea id="links_bulk" name="links_bulk" rows="6"
                    placeholder="https://twitter.com/...&#10;https://instagram.com/...&#10;https://youtube.com/channel/..."></textarea>
            </p>
            <p><small><?= e(t('artists.links.bulk_hint')) ?></small></p>
            <p><small><?= e(t('artists.links.youtube_hint')) ?></small></p>
            <button type="submit"><?= e(t('artists.links.submit')) ?></button>
        </form>
    </details>
<?php endif; ?>

<h2><?= e(t('artists.relations.title')) ?></h2>

<?php
$relationLabels = [
    'member_of'         => 'member_of_out',
    'former_member_of'  => 'former_member_of_out',
    'solo_project_of'   => 'solo_project_of_out',
    'collaborates_with' => 'collaborates_with',
];
$reverseLabels = [
    'member_of'         => 'member_of_in',
    'former_member_of'  => 'former_member_of_in',
    'solo_project_of'   => 'solo_project_of_in',
    'collaborates_with' => 'collaborates_with',
];
$hasRelations = !empty($outgoingRelations) || !empty($incomingRelations);
?>

<?php if (!$hasRelations): ?>
    <p><?= e(t('artists.relations.none')) ?></p>
<?php else: ?>
    <ul>
        <?php foreach ($outgoingRelations as $relation): ?>
            <li>
                <?= e(t('artists.relations.' . $relationLabels[$relation['relation_type']])) ?> :
                <a href="<?= url('/artists/' . $relation['other_slug']) ?>"><?= e($relation['other_name'] ?? $relation['other_slug']) ?></a>
                <?php if (!empty($relation['note'])): ?>
                    — <?= e($relation['note']) ?>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <form method="post" action="<?= url('/artists/' . $artist['id'] . '/relations/' . $relation['id'] . '/delete') ?>"
                          onsubmit="return confirm('<?= e(t('artists.relations.remove_confirm')) ?>');" style="display:inline">
                        <button type="submit"><?= e(t('artists.relations.remove')) ?></button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        <?php foreach ($incomingRelations as $relation): ?>
            <li>
                <?= e(t('artists.relations.' . $reverseLabels[$relation['relation_type']])) ?> :
                <a href="<?= url('/artists/' . $relation['other_slug']) ?>"><?= e($relation['other_name'] ?? $relation['other_slug']) ?></a>
                <?php if (!empty($relation['note'])): ?>
                    — <?= e($relation['note']) ?>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <form method="post" action="<?= url('/artists/' . $artist['id'] . '/relations/' . $relation['id'] . '/delete') ?>"
                          onsubmit="return confirm('<?= e(t('artists.relations.remove_confirm')) ?>');" style="display:inline">
                        <button type="submit"><?= e(t('artists.relations.remove')) ?></button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($editMode && !empty($otherArtists)): ?>
    <details>
        <summary><?= e(t('artists.relations.add_title')) ?></summary>
        <form method="post" action="<?= url('/artists/' . $artist['id'] . '/relations') ?>">
            <p>
                <label for="related_artist_id"><?= e(t('artists.relations.target_label')) ?></label><br>
                <select id="related_artist_id" name="related_artist_id" required>
                    <?php foreach ($otherArtists as $other): ?>
                        <option value="<?= (int) $other['id'] ?>"><?= e($other['name'] ?? $other['slug']) ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label for="relation_type"><?= e(t('artists.relations.type_label')) ?></label><br>
                <select id="relation_type" name="relation_type">
                    <option value="member_of"><?= e(t('artists.relations.member_of_out')) ?></option>
                    <option value="former_member_of"><?= e(t('artists.relations.former_member_of_out')) ?></option>
                    <option value="solo_project_of"><?= e(t('artists.relations.solo_project_of_out')) ?></option>
                    <option value="collaborates_with"><?= e(t('artists.relations.collaborates_with')) ?></option>
                </select>
            </p>
            <p>
                <label for="note"><?= e(t('artists.relations.note_label')) ?></label><br>
                <input type="text" id="note" name="note">
            </p>
            <button type="submit"><?= e(t('artists.relations.submit')) ?></button>
        </form>
    </details>
<?php endif; ?>

<?php if (\App\Core\Auth::check()): ?>
    <details>
        <summary><?= e(t('artists.report_this')) ?></summary>
        <form method="post" action="<?= url('/reports') ?>">
            <input type="hidden" name="reportable_type" value="artist">
            <input type="hidden" name="reportable_id" value="<?= (int) $artist['id'] ?>">
            <p>
                <label for="reason"><?= e(t('report.reason')) ?></label><br>
                <select id="reason" name="reason">
                    <option value="duplicate"><?= e(t('report.reason.duplicate')) ?></option>
                    <option value="wrong_info"><?= e(t('report.reason.wrong_info')) ?></option>
                    <option value="spam"><?= e(t('report.reason.spam')) ?></option>
                    <option value="inappropriate"><?= e(t('report.reason.inappropriate')) ?></option>
                    <option value="other"><?= e(t('report.reason.other')) ?></option>
                </select>
            </p>
            <p>
                <label for="comment"><?= e(t('report.comment')) ?></label><br>
                <textarea id="comment" name="comment" rows="3"></textarea>
            </p>
            <button type="submit"><?= e(t('report.submit')) ?></button>
        </form>
    </details>
<?php endif; ?>

<p><a href="<?= url('/artists') ?>"><?= e(t('artists.back')) ?></a></p>
