<h1><?= e(t('artists.title')) ?></h1>

<p><a href="<?= url('/artists/create') ?>"><?= e(t('artists.add')) ?></a></p>

<?php if (empty($artists)): ?>
    <p><?= e(t('artists.empty')) ?></p>
<?php else: ?>
    <ul>
        <?php foreach ($artists as $artist): ?>
            <li>
                <a href="<?= url('/artists/' . $artist['slug']) ?>"><?= e($artist['name'] ?? $artist['slug']) ?></a>
                <small>(<?= e(t('artists.type.' . $artist['type'])) ?>)</small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
