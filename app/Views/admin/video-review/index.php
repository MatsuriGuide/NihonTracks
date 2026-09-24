<h1><?= e(t('admin.video_review.title')) ?></h1>

<?php
$formatDuration = static function (int $seconds): string {
    $minutes = intdiv($seconds, 60);
    $secs = $seconds % 60;

    if ($minutes >= 60) {
        $hours = intdiv($minutes, 60);
        $minutes %= 60;

        return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
    }

    return sprintf('%d:%02d', $minutes, $secs);
};
?>

<p><small><?= e(t('admin.video_review.hint')) ?></small></p>

<form method="get" action="<?= url('/admin/video-review') ?>">
    <input type="text" name="q" value="<?= e($titleQuery ?? '') ?>"
           placeholder="<?= e(t('videos.filter.title_search_placeholder')) ?>" style="width: 100%; max-width: 400px;">
    <button type="submit"><?= e(t('videos.filter.apply')) ?></button>
    <?php if (!empty($titleQuery)): ?>
        <a href="<?= url('/admin/video-review') ?>"><?= e(t('videos.filter.reset')) ?></a>
    <?php endif; ?>
</form>

<?php if (empty($videos)): ?>
    <p>
        <?php if (!empty($titleQuery)): ?>
            <?= e(t('videos.filter.no_results')) ?>
        <?php else: ?>
            <?= e(t('admin.video_review.none')) ?>
        <?php endif; ?>
    </p>
<?php else: ?>
    <ul class="card-grid">
        <?php foreach ($videos as $index => $video): ?>
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
                        <?php if (!empty($video['duration_seconds'])): ?>
                            <strong><?= e($formatDuration((int) $video['duration_seconds'])) ?></strong> —
                        <?php endif; ?>
                        <?php if (!empty($video['artist_names'])): ?>
                            <?= e($video['artist_names']) ?>
                        <?php endif; ?>
                        <?php if (!empty($video['release_date'])): ?>
                            — <?= e($video['release_date']) ?>
                        <?php endif; ?>
                    </span>

                    <div class="video-review-preview" id="preview-<?= (int) $video['id'] ?>">
                        <?php if ($index === 0): ?>
                            <iframe width="100%" height="180"
                                    src="https://www.youtube.com/embed/<?= e($video['youtube_id']) ?>"
                                    title="<?= e($video['title'] ?? $video['youtube_id']) ?>"
                                    frameborder="0" allowfullscreen loading="lazy"></iframe>
                        <?php else: ?>
                            <button type="button" class="btn-small video-preview-toggle"
                                    data-youtube-id="<?= e($video['youtube_id']) ?>"
                                    data-target="preview-<?= (int) $video['id'] ?>">
                                <?= e(t('admin.video_review.preview')) ?>
                            </button>
                        <?php endif; ?>
                    </div>

                    <form method="post" action="<?= url('/admin/video-review/' . $video['id'] . '/validate') ?>">
                        <?php if (!empty($titleQuery)): ?>
                            <input type="hidden" name="q" value="<?= e($titleQuery) ?>">
                        <?php endif; ?>
                        <input type="hidden" name="page" value="<?= (int) $page ?>">
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

    <script>
        document.querySelectorAll('.video-preview-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = document.getElementById(btn.dataset.target);
                var iframe = document.createElement('iframe');
                iframe.width = '100%';
                iframe.height = '180';
                iframe.src = 'https://www.youtube.com/embed/' + btn.dataset.youtubeId + '?autoplay=1';
                iframe.frameBorder = '0';
                iframe.allow = 'autoplay; encrypted-media';
                iframe.allowFullscreen = true;
                target.innerHTML = '';
                target.appendChild(iframe);
            });
        });
    </script>

    <?php if ($totalPages > 1): ?>
        <?php $pageQuery = !empty($titleQuery) ? '&q=' . urlencode($titleQuery) : ''; ?>
        <style>
            .pagination { display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0; }
        </style>
        <nav class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?= url('/admin/video-review?page=' . ($page - 1) . $pageQuery) ?>"><?= e(t('pagination.previous')) ?></a>
            <?php endif; ?>
            <span class="mono"><?= e(t('pagination.page_of')) ?> <?= (int) $page ?> / <?= (int) $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="<?= url('/admin/video-review?page=' . ($page + 1) . $pageQuery) ?>"><?= e(t('pagination.next')) ?></a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
