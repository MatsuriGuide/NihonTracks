<h1>Artistes</h1>

<p><a href="<?= url('/artists/create') ?>">+ Ajouter un artiste</a></p>

<?php if (empty($artists)): ?>
    <p>Aucun artiste pour le moment.</p>
<?php else: ?>
    <ul>
        <?php foreach ($artists as $artist): ?>
            <li>
                <a href="<?= url('/artists/' . $artist['slug']) ?>"><?= e($artist['name'] ?? $artist['slug']) ?></a>
                <small>(<?= e($artist['type']) ?>)</small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
