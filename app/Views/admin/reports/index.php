<h1><?= e(t('admin.reports.title')) ?></h1>

<h2><?= e(t('admin.reports.pending_title')) ?> (<?= count($pending) ?>)</h2>

<?php if (empty($pending)): ?>
    <p><?= e(t('admin.reports.none_pending')) ?></p>
<?php else: ?>
    <?php foreach ($pending as $report): ?>
        <div class="report">
            <p>
                <strong><?= e(ucfirst($report['reportable_type'])) ?></strong> :
                <?= e($report['content_label']) ?>
                <?php if (!empty($report['content_url'])): ?>
                    (<a href="<?= e($report['content_url']) ?>" target="_blank" rel="noopener"><?= e(t('admin.reports.view')) ?></a>)
                <?php endif; ?>
            </p>
            <p>
                <?= e(t('admin.reports.reason')) ?> <?= e(t('report.reason.' . $report['reason'])) ?>
                <?php if (!empty($report['comment'])): ?>
                    — « <?= e($report['comment']) ?> »
                <?php endif; ?>
            </p>
            <p><?= e(t('admin.reports.reported_by')) ?> <?= e($report['reporter_name']) ?> <?= e(t('admin.reports.on')) ?> <?= e($report['created_at']) ?></p>

            <form method="post" action="<?= url('/admin/reports/' . $report['id'] . '/resolve') ?>" style="display:inline">
                <input type="hidden" name="action" value="dismiss">
                <button type="submit"><?= e(t('admin.reports.dismiss')) ?></button>
            </form>

            <?php if ($report['reportable_type'] === 'video'): ?>
                <form method="post" action="<?= url('/admin/reports/' . $report['id'] . '/resolve') ?>" style="display:inline">
                    <input type="hidden" name="action" value="hide">
                    <button type="submit"><?= e(t('admin.reports.hide_video')) ?></button>
                </form>
            <?php endif; ?>

            <form method="post" action="<?= url('/admin/reports/' . $report['id'] . '/resolve') ?>" style="display:inline"
                  onsubmit="return confirm('<?= e(t('admin.reports.delete_confirm')) ?>');">
                <input type="hidden" name="action" value="delete">
                <button type="submit"><?= e(t('admin.reports.delete_content')) ?></button>
            </form>
        </div>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>

<h2><?= e(t('admin.reports.history_title')) ?></h2>

<?php if (empty($resolved)): ?>
    <p><?= e(t('admin.reports.no_history')) ?></p>
<?php else: ?>
    <ul>
        <?php foreach ($resolved as $report): ?>
            <li>
                <?= e(ucfirst($report['reportable_type'])) ?> « <?= e($report['content_label']) ?> »
                — <?= e($report['status']) ?>
                <?= e(t('admin.reports.by')) ?> <?= e($report['resolver_name'] ?? '?') ?>
                <?= e(t('admin.reports.on')) ?> <?= e($report['resolved_at']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
