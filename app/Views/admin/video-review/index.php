<h1><?= e(t('admin.video_review.title')) ?></h1>

<p><small><?= e(t('admin.video_review.hint')) ?></small></p>

<?php if (empty($videos)): ?>
    <p><?= e(t('admin.video_review.none')) ?></p>
<?php else: ?>
    <ul class="card-grid">
        <?php foreach ($videos as $video): ?>
            <li class="card">
                <a href="<?= url('/videos/' . $video['id']) ?>" target="_blank">
                    <?php if (!empty($video['thumbnail_url'])): ?>
                        <div class="card-thumb" style="background-image:url('<?= e($video['thumbnail_url']) ?>');"></div>
                    <?php else: ?>
                        <div class="card-thumb"></div>
                    <?php endif; ?>
                </a>
                <div class="card-body">
                    <a href="<?= url('/videos/' . $video['id']) ?>" target="_blank" class="card-title">
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

                    <form method="post" action="<?= url('/admin/video-review/' . $video['id'] . '/validate') ?>">
                        <p>
                            <select name="video_type">
                                <?php foreach ($videoTypes as $typeValue): ?>
                                    <option value="<?= e($typeValue) ?>" <?= $video['video_type'] === $typeValue ? 'selected' : '' ?>>
                                        <?= e(t('videos.type.' . $typeValue)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                        <p>
                            <button type="submit" class="btn-small"><?= e(t('admin.video_review.validate')) ?></button>
                            <a href="<?= url('/videos/' . $video['id'] . '/edit') ?>" class="btn-small">
                                <?= e(t('admin.video_review.edit')) ?>
                            </a>
                        </p>
                    </form>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($totalPages > 1): ?>
    <style>
        .pagination { display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0; }
    </style>
    <nav class="pagination">
        <?php if ($page > 1): ?>
            <a href="<?= url('/admin/video-review?page=' . ($page - 1)) ?>"><?= e(t('pagination.previous')) ?></a>
        <?php endif; ?>
        <span class="mono"><?= e(t('pagination.page_of')) ?> <?= (int) $page ?> / <?= (int) $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
            <a href="<?= url('/admin/video-review?page=' . ($page + 1)) ?>"><?= e(t('pagination.next')) ?></a>
        <?php endif; ?>
    </nav>
<?php endif; ?>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
