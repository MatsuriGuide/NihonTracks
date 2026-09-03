<h1><?= e(t('artists.title')) ?></h1>

<?php $hiddenVideos = (int) ($_GET['hidden_videos'] ?? 0); ?>
<?php if ($hiddenVideos > 0): ?>
    <p class="hint hint-success">
        <?= e(t('artists.delete_notice_1')) ?> <?= $hiddenVideos ?> <?= e(t('artists.delete_notice_2')) ?>
    </p>
<?php endif; ?>

<p>
    <a href="<?= url('/artists/create') ?>" class="btn"><?= e(t('artists.add')) ?></a>
    &nbsp;
    <a href="<?= url('/artists/quick-create') ?>" class="btn btn-small"><?= e(t('artists.quick_create.link')) ?></a>
</p>

<?php if (empty($artists)): ?>
    <p><?= e(t('artists.empty')) ?></p>
<?php else: ?>
    <input type="text" id="artist-list-search" placeholder="<?= e(t('artists.search_placeholder')) ?>"
           autocomplete="off" style="width: 100%; max-width: 400px; margin-bottom: 1rem;">

    <ul class="card-grid" id="artist-list">
        <?php foreach ($artists as $artist): ?>
            <li class="card artist-list-item" data-name="<?= e(mb_strtolower($artist['name'] ?? $artist['slug'])) ?>">
                <a href="<?= url('/artists/' . $artist['slug']) ?>">
                    <?php if (!empty($artist['avatar_path'])): ?>
                        <div class="card-thumb" style="background-image:url('<?= e($artist['avatar_path']) ?>'); aspect-ratio: 1 / 1;"></div>
                    <?php else: ?>
                        <div class="card-thumb" style="aspect-ratio: 1 / 1;"></div>
                    <?php endif; ?>
                </a>
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

    <script src="<?= asset('js/artist-list-filter.js') ?>"></script>
<?php endif; ?>
