<h1><?= e(t('admin.video_review.title')) ?></h1>

<p><small><?= e(t('admin.video_review.hint')) ?></small></p>

<?php if (empty($videos)): ?>
    <p><?= e(t('admin.video_review.none')) ?></p>
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
                    <span class="card-title"><?= e($video['title'] ?? $video['youtube_id']) ?></span>
                    <span class="card-meta">
                        <?php if (!empty($video['artist_names'])): ?>
                            <?= e($video['artist_names']) ?>
                        <?php endif; ?>
                        — <?= e(t('videos.type.' . $video['video_type'])) ?>
                        <?php if (!empty($video['release_date'])): ?>
                            — <?= e($video['release_date']) ?>
                        <?php endif; ?>
                    </span>
                    <p>
                        <a href="<?= url('/videos/' . $video['id'] . '/edit') ?>" class="btn btn-small">
                            <?= e(t('admin.video_review.edit')) ?>
                        </a>
                        <form method="post" action="<?= url('/admin/video-review/' . $video['id'] . '/mark-reviewed') ?>" style="display:inline">
                            <button type="submit" class="btn-small"><?= e(t('admin.video_review.mark_reviewed')) ?></button>
                        </form>
                    </p>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
