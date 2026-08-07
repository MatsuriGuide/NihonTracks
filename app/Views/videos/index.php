<h1><?= e(t('videos.title')) ?></h1>

<p><a href="<?= url('/videos/create') ?>" class="btn"><?= e(t('videos.add')) ?></a></p>

<?php if (empty($videos)): ?>
    <p><?= e(t('videos.empty')) ?></p>
<?php else: ?>
    <ul class="card-grid">
        <?php foreach ($videos as $video): ?>
            <li class="card">
                <?php if (!empty($video['thumbnail_url'])): ?>
                    <div class="card-thumb" style="background-image:url('<?= e($video['thumbnail_url']) ?>');"></div>
                <?php else: ?>
                    <div class="card-thumb"></div>
                <?php endif; ?>
                <div class="card-body">
                    <span class="catalog-no is-muted"><?= e(catalog_no('v', (int) $video['id'])) ?></span>
                    <a href="<?= url('/videos/' . $video['id']) ?>" class="card-title">
                        <?= e($video['title'] ?? $video['youtube_id']) ?>
                    </a>
                    <span class="card-meta">
                        <?php if (!empty($video['artist_names'])): ?>
                            <?= e($video['artist_names']) ?>
                        <?php endif; ?>
                        <?php if (!empty($video['release_date'])): ?>
                            — <?= e($video['release_date']) ?>
                        <?php endif; ?>
                    </span>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
