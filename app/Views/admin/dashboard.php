<h1><?= e(t('admin.dashboard_title')) ?></h1>

<p><?= e(t('admin.pending_reports')) ?> <strong><?= (int) $pendingReports ?></strong></p>
<p><?= e(t('admin.videos_needing_review')) ?> <strong><?= (int) $videosNeedingReview ?></strong></p>
<p><?= e(t('admin.pending_artists')) ?> <strong><?= (int) $pendingArtists ?></strong></p>
<p><?= e(t('admin.pending_incomplete')) ?> <strong><?= (int) $incompleteArtists ?></strong></p>

<ul>
    <li><a href="<?= url('/admin/reports') ?>"><?= e(t('admin.reports_link')) ?></a></li>
    <li><a href="<?= url('/admin/video-review') ?>"><?= e(t('admin.video_review_link')) ?></a></li>
    <li><a href="<?= url('/admin/artist-approvals') ?>"><?= e(t('admin.artist_approvals_link')) ?></a></li>
    <li><a href="<?= url('/admin/incomplete-artists') ?>"><?= e(t('admin.incomplete_link')) ?></a></li>
    <li><a href="<?= url('/admin/watch') ?>"><?= e(t('admin.watch_link')) ?></a></li>
    <li><a href="<?= url('/admin/tags') ?>"><?= e(t('admin.tags_link')) ?></a></li>
    <li><a href="<?= url('/admin/csv-export') ?>"><?= e(t('admin.csv_export_link')) ?></a></li>
</ul>
