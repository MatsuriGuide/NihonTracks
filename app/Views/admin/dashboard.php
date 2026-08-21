<h1><?= e(t('admin.dashboard_title')) ?></h1>

<p><?= e(t('admin.pending_reports')) ?> <strong><?= (int) $pendingReports ?></strong></p>
<p><?= e(t('admin.pending_suggestions')) ?> <strong><?= (int) $pendingSuggestions ?></strong></p>
<p><?= e(t('admin.pending_artists')) ?> <strong><?= (int) $pendingArtists ?></strong></p>

<ul>
    <li><a href="<?= url('/admin/reports') ?>"><?= e(t('admin.reports_link')) ?></a></li>
    <li><a href="<?= url('/admin/suggestions') ?>"><?= e(t('admin.suggestions_link')) ?></a></li>
    <li><a href="<?= url('/admin/artist-approvals') ?>"><?= e(t('admin.artist_approvals_link')) ?></a></li>
    <li><a href="<?= url('/admin/watch') ?>"><?= e(t('admin.watch_link')) ?></a></li>
    <li><a href="<?= url('/admin/tags') ?>"><?= e(t('admin.tags_link')) ?></a></li>
</ul>
