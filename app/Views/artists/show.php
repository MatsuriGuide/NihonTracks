<h1><?= e($translation['name'] ?? $artist['slug']) ?></h1>

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
