<h1><?= e(t('admin.tag_backfill.title')) ?></h1>

<p><small><?= e(t('admin.tag_backfill.hint')) ?></small></p>

<?php if ($result !== null): ?>
    <p class="hint hint-success">
        <?= e(t('admin.tag_backfill.result_processed')) ?> <?= (int) $result['processed'] ?>
        — <?= e(t('admin.tag_backfill.result_updated')) ?> <?= (int) $result['updated'] ?>
    </p>
<?php endif; ?>

<p>
    <?= e(t('admin.tag_backfill.remaining')) ?> <strong><?= (int) $candidateCount ?></strong>
</p>

<?php if ($candidateCount > 0): ?>
    <form method="post" action="<?= url('/admin/video-tag-backfill/run') ?>">
        <button type="submit"><?= e(t('admin.tag_backfill.run')) ?></button>
    </form>
    <p><small><?= e(t('admin.tag_backfill.batch_hint')) ?></small></p>
<?php else: ?>
    <p><?= e(t('admin.tag_backfill.none')) ?></p>
<?php endif; ?>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
