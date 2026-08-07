<h1><?= e(t('videos.title')) ?></h1>

<p><a href="<?= url('/videos/create') ?>"><?= e(t('videos.add')) ?></a></p>

<?php if (empty($videos)): ?>
    <p><?= e(t('videos.empty')) ?></p>
<?php else: ?>
    <ul>
        <?php foreach ($videos as $video): ?>
            <li>
                <a href="<?= url('/videos/' . $video['id']) ?>">
                    <?= e($video['title'] ?? $video['youtube_id']) ?>
                </a>
                <?php if (!empty($video['artist_names'])): ?>
                    — <?= e($video['artist_names']) ?>
                <?php endif; ?>
                <?php if (!empty($video['release_date'])): ?>
                    — <?= e($video['release_date']) ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
