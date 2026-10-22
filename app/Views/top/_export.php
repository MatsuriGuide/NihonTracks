<?php
/**
 * Formulaire d'export CSV du TOP — visible uniquement des modérateurs/admins.
 * Attend : $weekDate, $maxSubscribers (facultatif), $exportDefaultType.
 */
if (!\App\Core\Auth::check() || !in_array(\App\Core\Auth::role(), ['moderator', 'admin'], true)) {
    return;
}

$exportDefaultType = $exportDefaultType ?? 'videos';
?>
<form method="get" action="<?= url('/top/export.csv') ?>" style="margin: 1.5rem 0; padding: .75rem; border: 1px solid rgba(128,128,128,.35); border-radius: 6px;">
    <strong><?= e(t('top.export_title')) ?></strong>
    <input type="hidden" name="week" value="<?= e($weekDate) ?>">
    <input type="hidden" name="max" value="<?= (int) ($maxSubscribers ?? 10000) ?>">
    <select name="type" aria-label="<?= e(t('top.export_title')) ?>">
        <option value="videos"<?= $exportDefaultType === 'videos' ? ' selected' : '' ?>><?= e(t('top.export_type_videos')) ?></option>
        <option value="progress"<?= $exportDefaultType === 'progress' ? ' selected' : '' ?>><?= e(t('top.export_type_progress')) ?></option>
        <option value="artists"<?= $exportDefaultType === 'artists' ? ' selected' : '' ?>><?= e(t('top.export_type_artists')) ?></option>
    </select>
    <label><?= e(t('top.export_count')) ?>
        <input type="number" name="limit" value="10" min="1" max="50" style="width: 4.5rem;">
    </label>
    <button type="submit"><?= e(t('top.export_button')) ?></button>
</form>
