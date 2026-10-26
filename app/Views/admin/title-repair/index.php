<h1><?= e(t('admin.title_repair.title')) ?></h1>

<p><small><?= e(t('admin.title_repair.hint')) ?></small></p>

<?php if ($result !== null): ?>
    <p class="hint hint-success">
        <?= e(t('admin.title_repair.processed')) ?> <?= (int) $result['processed'] ?>
        — <?= e(t('admin.title_repair.fixed')) ?> <?= (int) $result['fixed'] ?>
        — <?= e(t('admin.title_repair.thumbs_fixed')) ?> <?= (int) $result['thumbs'] ?>
    </p>
<?php endif; ?>

<p><?= e(t('admin.title_repair.remaining')) ?> <strong><?= (int) $remaining ?></strong>
   · <?= e(t('admin.title_repair.missing_thumbs')) ?> <strong><?= (int) $missingThumbs ?></strong></p>

<?php if ($remaining > 0 || $missingThumbs > 0): ?>
    <form method="post" action="<?= url('/admin/title-repair/run') ?>">
        <button type="submit"><?= e(t('admin.title_repair.run')) ?></button>
    </form>
    <p><small><?= e(t('admin.title_repair.batch_hint')) ?></small></p>
<?php else: ?>
    <p><?= e(t('admin.title_repair.none')) ?></p>
<?php endif; ?>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
