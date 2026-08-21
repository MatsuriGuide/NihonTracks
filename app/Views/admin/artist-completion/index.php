<h1><?= e(t('admin.artist_completion.title')) ?></h1>

<?php if (empty($artists)): ?>
    <p><?= e(t('admin.artist_completion.none')) ?></p>
<?php else: ?>
    <ul>
        <?php foreach ($artists as $artist): ?>
            <li>
                <a href="<?= url('/artists/' . $artist['slug']) ?>" target="_blank"><?= e($artist['name'] ?? $artist['slug']) ?></a>
                —
                <?php if (empty($artist['bio_fr'])): ?>
                    <span class="tag"><?= e(t('admin.artist_completion.missing_bio')) ?></span>
                <?php endif; ?>
                <?php if (empty($artist['start_year'])): ?>
                    <span class="tag"><?= e(t('admin.artist_completion.missing_year')) ?></span>
                <?php endif; ?>
                <a href="<?= url('/artists/' . $artist['id'] . '/edit') ?>"><?= e(t('admin.artist_completion.edit')) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
