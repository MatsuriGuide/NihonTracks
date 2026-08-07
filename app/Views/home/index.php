<h1><?= e(t('home.title')) ?></h1>

<?php if (empty($latestVideos)): ?>
    <p><?= e(t('home.empty')) ?></p>
<?php else: ?>
    <ul>
        <?php foreach ($latestVideos as $video): ?>
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
                (<a href="https://youtube.com/watch?v=<?= e($video['youtube_id']) ?>" target="_blank" rel="noopener">YouTube</a>)
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
