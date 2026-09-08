<h1><?= e(t('admin.exports.title')) ?></h1>

<p>
    <?= e(t('admin.exports.stats_artists')) ?> <strong><?= (int) $artistCount ?></strong>
    &nbsp;·&nbsp;
    <?= e(t('admin.exports.stats_videos')) ?> <strong><?= (int) $videoCount ?></strong>
    &nbsp;·&nbsp;
    <?= e(t('admin.exports.stats_playlists')) ?> <strong><?= (int) $playlistCount ?></strong>
</p>
<p><small><?= e(t('admin.exports.generated_at')) ?> <?= e($generatedAt) ?></small></p>

<h2><?= e(t('admin.exports.section_artists')) ?></h2>
<p>
    <a href="<?= url('/admin/exports/artists.json') ?>" class="btn btn-small">JSON</a>
    <a href="<?= url('/admin/exports/artists.csv') ?>" class="btn btn-small">CSV</a>
</p>

<h2><?= e(t('admin.exports.section_videos')) ?></h2>
<p>
    <a href="<?= url('/admin/exports/videos.json') ?>" class="btn btn-small">JSON</a>
    <a href="<?= url('/admin/exports/videos.csv') ?>" class="btn btn-small">CSV</a>
</p>

<h2><?= e(t('admin.exports.section_taxonomy')) ?></h2>
<p><a href="<?= url('/admin/exports/taxonomy.json') ?>" class="btn btn-small">JSON</a></p>

<h2><?= e(t('admin.exports.section_playlists')) ?></h2>
<p><a href="<?= url('/admin/exports/playlists.json') ?>" class="btn btn-small">JSON</a></p>

<h2><?= e(t('admin.exports.section_full')) ?></h2>
<p><a href="<?= url('/admin/exports/full.json') ?>" class="btn">📦 <?= e(t('admin.exports.full_button')) ?></a></p>
<p><small><?= e(t('admin.exports.full_hint')) ?></small></p>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
