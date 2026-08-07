<h1><span class="catalog-no"><?= e(catalog_no('a', (int) $artist['id'])) ?></span><?= e($translation['name'] ?? $artist['slug']) ?></h1>

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

<?php if (\App\Core\Auth::canEdit((int) $artist['created_by'])): ?>
    <p>
        <a href="<?= url('/artists/' . $artist['id'] . '/edit') ?>"><?= e(t('artists.edit')) ?></a>
        &nbsp;
        <form method="post" action="<?= url('/artists/' . $artist['id'] . '/delete') ?>"
              onsubmit="return confirm('<?= e(t('artists.delete_confirm')) ?>');" style="display:inline">
            <button type="submit"><?= e(t('artists.delete')) ?></button>
        </form>
    </p>
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
                <?php if (\App\Core\Auth::canEdit((int) $artist['created_by'])): ?>
                    <form method="post" action="<?= url('/artists/' . $artist['id'] . '/links/' . $link['id'] . '/delete') ?>"
                          onsubmit="return confirm('<?= e(t('artists.links.remove_confirm')) ?>');" style="display:inline">
                        <button type="submit"><?= e(t('artists.links.remove')) ?></button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (\App\Core\Auth::canEdit((int) $artist['created_by'])): ?>
    <details>
        <summary><?= e(t('artists.links.add_title')) ?></summary>
        <form method="post" action="<?= url('/artists/' . $artist['id'] . '/links') ?>">
            <p>
                <label for="platform"><?= e(t('artists.links.platform_label')) ?></label><br>
                <select id="platform" name="platform">
                    <?php foreach (\App\Models\ArtistLink::PLATFORMS as $platform): ?>
                        <option value="<?= e($platform) ?>"><?= e(t('artists.links.platform.' . $platform)) ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label for="url"><?= e(t('artists.links.url_label')) ?></label><br>
                <input type="url" id="url" name="url" placeholder="https://..." required>
            </p>
            <p><small><?= e(t('artists.links.youtube_hint')) ?></small></p>
            <button type="submit"><?= e(t('artists.links.submit')) ?></button>
        </form>
    </details>
<?php endif; ?>

<h2><?= e(t('artists.relations.title')) ?></h2>

<?php
$relationLabels = [
    'member_of'        => 'member_of_out',
    'former_member_of' => 'former_member_of_out',
    'solo_project_of'  => 'solo_project_of_out',
    'collaborates_with' => 'collaborates_with',
];
$reverseLabels = [
    'member_of'        => 'member_of_in',
    'former_member_of' => 'former_member_of_in',
    'solo_project_of'  => 'solo_project_of_in',
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
                <?php if (\App\Core\Auth::canEdit((int) $artist['created_by'])): ?>
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
                <?php if (\App\Core\Auth::canEdit((int) $artist['created_by'])): ?>
                    <form method="post" action="<?= url('/artists/' . $artist['id'] . '/relations/' . $relation['id'] . '/delete') ?>"
                          onsubmit="return confirm('<?= e(t('artists.relations.remove_confirm')) ?>');" style="display:inline">
                        <button type="submit"><?= e(t('artists.relations.remove')) ?></button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (\App\Core\Auth::canEdit((int) $artist['created_by']) && !empty($otherArtists)): ?>
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

<?php if (\App\Core\Auth::role() === 'admin'): ?>
    <div class="admin-translate">
        <h3><?= e(t('admin.translate.title')) ?></h3>
        <?php foreach (['en', 'ja'] as $targetLang): ?>
            <?php $existing = $allTranslations[$targetLang] ?? null; ?>
            <form method="post" action="<?= url('/admin/translate/artist/' . $artist['id'] . '/' . $targetLang) ?>" style="display:inline">
                <button type="submit">
                    <?= $existing ? e(t('admin.translate.retranslate')) : e(t('admin.translate.translate')) ?>
                    (<?= e(strtoupper($targetLang)) ?>)
                </button>
            </form>
            <?php if (!empty($existing['is_auto_translated'])): ?>
                <small><?= e(t('admin.translate.auto_flag')) ?></small>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
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
