<h1><?= e(t('admin.top.title')) ?></h1>

<p><small><?= e(t('admin.top.hint')) ?></small></p>

<?php if ($result !== null): ?>
    <p class="hint hint-success">
        <?php if ($result['type'] === 'baseline'): ?>
            <?= e(t('admin.top.result_baseline')) ?> <?= (int) $result['stored'] ?> / <?= (int) $result['candidates'] ?>
        <?php elseif ($result['type'] === 'run'): ?>
            <?= e(t('admin.top.result_run')) ?> <?= (int) $result['measured'] ?> — <?= e(t('admin.top.result_ranked')) ?> <?= (int) $result['ranked'] ?>
        <?php elseif ($result['type'] === 'already'): ?>
            <?= e(t('admin.top.result_already')) ?>
        <?php else: ?>
            <?= e(t('admin.top.result_failed')) ?>
        <?php endif; ?>
    </p>
<?php endif; ?>

<ul>
    <li><?= e(t('admin.top.current_week')) ?> <strong><?= e($weekDate) ?></strong></li>
    <li><?= e(t('admin.top.last_run')) ?> <strong><?= e($lastRun ?? '—') ?></strong>
        <?= $due ? '— ' . e(t('admin.top.due')) : '— ✓' ?></li>
    <li><?= e(t('admin.top.without_baseline')) ?> <strong><?= (int) $withoutBaseline ?></strong></li>
</ul>

<h2><?= e(t('admin.top.step1')) ?></h2>
<p><small><?= e(t('admin.top.step1_hint')) ?></small></p>
<form method="post" action="<?= url('/admin/top/baseline') ?>">
    <button type="submit"><?= e(t('admin.top.baseline_button')) ?></button>
</form>

<h2><?= e(t('admin.top.step2')) ?></h2>
<p><small><?= e(t('admin.top.step2_hint')) ?></small></p>
<?php if ($due): ?>
    <form method="post" action="<?= url('/admin/top/run') ?>"
          onsubmit="return confirm('<?= e(t('admin.top.run_confirm')) ?>');">
        <button type="submit"><?= e(t('admin.top.run_button')) ?></button>
    </form>
<?php else: ?>
    <p><?= e(t('admin.top.result_already')) ?></p>
<?php endif; ?>

<p><a href="<?= url('/top') ?>"><?= e(t('admin.top.see_top')) ?></a> ·
   <a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
