<h1><?= e(t('admin.dashboard_title')) ?></h1>

<p><?= e(t('admin.pending_reports')) ?> <strong><?= (int) $pendingReports ?></strong></p>

<ul>
    <li><a href="<?= url('/admin/reports') ?>"><?= e(t('admin.reports_link')) ?></a></li>
    <li><a href="<?= url('/admin/tags') ?>"><?= e(t('admin.tags_link')) ?></a></li>
</ul>
