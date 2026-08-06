<h1>Dernières sorties</h1>

<?php if (empty($latestVideos)): ?>
    <p>Aucune vidéo pour le moment.</p>
<?php else: ?>
    <ul>
        <?php foreach ($latestVideos as $video): ?>
            <li>
                <a href="https://youtube.com/watch?v=<?= e($video['youtube_id']) ?>" target="_blank" rel="noopener">
                    <?= e($video['youtube_id']) ?>
                </a>
                — <?= e($video['release_date']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
