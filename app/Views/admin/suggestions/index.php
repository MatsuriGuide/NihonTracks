<h1><?= e(t('admin.suggestions.title')) ?></h1>

<?php if (empty($suggestions)): ?>
    <p><?= e(t('admin.suggestions.none')) ?></p>
<?php else: ?>
    <ul class="card-grid">
        <?php foreach ($suggestions as $suggestion): ?>
            <li class="card">
                <a href="https://www.youtube.com/watch?v=<?= e($suggestion['youtube_id']) ?>" target="_blank" rel="noopener">
                    <?php if (!empty($suggestion['thumbnail_url'])): ?>
                        <div class="card-thumb" style="background-image:url('<?= e($suggestion['thumbnail_url']) ?>');"></div>
                    <?php else: ?>
                        <div class="card-thumb"></div>
                    <?php endif; ?>
                </a>
                <div class="card-body">
                    <span class="card-title"><?= e($suggestion['title'] ?? $suggestion['youtube_id']) ?></span>
                    <span class="card-meta">
                        <a href="<?= url('/artists/' . $suggestion['artist_slug']) ?>"><?= e($suggestion['artist_name'] ?? '') ?></a>
                        <?php if (!empty($suggestion['published_at'])): ?>
                            — <?= e($suggestion['published_at']) ?>
                        <?php endif; ?>
                    </span>
                    <p>
                        <a href="https://www.youtube.com/watch?v=<?= e($suggestion['youtube_id']) ?>" target="_blank" rel="noopener" class="card-meta">
                            <?= e(t('admin.suggestions.watch')) ?> ↗
                        </a>
                    </p>
                    <p>
                        <a href="<?= url('/admin/suggestions/' . $suggestion['id'] . '/publish') ?>" class="btn btn-small">
                            <?= e(t('admin.suggestions.publish')) ?>
                        </a>
                        <form method="post" action="<?= url('/admin/suggestions/' . $suggestion['id'] . '/dismiss') ?>" style="display:inline">
                            <button type="submit" class="btn-small"><?= e(t('admin.suggestions.dismiss')) ?></button>
                        </form>
                    </p>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
