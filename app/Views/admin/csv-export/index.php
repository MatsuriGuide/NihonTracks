<h1><?= e(t('admin.csv_export.title')) ?></h1>

<p><small><?= e(t('admin.csv_export.hint')) ?></small></p>

<?php if ($lastExport !== null): ?>
    <p><small><?= e(t('admin.csv_export.last_export')) ?> <?= e($lastExport) ?></small></p>
<?php endif; ?>

<form method="post" action="<?= url('/admin/csv-export/generate') ?>">
    <p>
        <label for="since"><?= e(t('admin.csv_export.since_label')) ?></label><br>
        <input type="datetime-local" id="since" name="since" value="<?= e($defaultSince) ?>">
    </p>
    <button type="submit"><?= e(t('admin.csv_export.generate')) ?></button>
</form>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
