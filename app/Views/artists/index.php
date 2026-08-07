<h1><?= e(t('artists.title')) ?></h1>

<p><a href="<?= url('/artists/create') ?>" class="btn"><?= e(t('artists.add')) ?></a></p>

<?php if (empty($artists)): ?>
    <p><?= e(t('artists.empty')) ?></p>
<?php else: ?>
    <ul class="card-grid">
        <?php foreach ($artists as $artist): ?>
            <li class="card">
                <div class="card-body">
                    <span class="catalog-no is-muted"><?= e(catalog_no('a', (int) $artist['id'])) ?></span>
                    <a href="<?= url('/artists/' . $artist['slug']) ?>" class="card-title">
                        <?= e($artist['name'] ?? $artist['slug']) ?>
                    </a>
                    <span class="card-meta"><?= e(t('artists.type.' . $artist['type'])) ?></span>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
