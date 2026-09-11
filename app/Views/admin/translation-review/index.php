<h1><?= e(t('admin.translation_review.title')) ?></h1>

<p><small><?= e(t('admin.translation_review.hint')) ?></small></p>

<?php if (empty($artists)): ?>
    <p><?= e(t('admin.translation_review.none')) ?></p>
<?php else: ?>
    <ul>
        <?php foreach ($artists as $artist): ?>
            <li style="margin-bottom: 1.5rem;">
                <a href="<?= url('/artists/' . $artist['slug']) ?>" target="_blank">
                    <strong><?= e($artist['name'] ?? $artist['slug']) ?></strong>
                </a>

                <?php if (empty($artist['has_en'])): ?>
                    <form method="post" action="<?= url('/admin/translate/artist/' . $artist['id'] . '/en') ?>" style="display:inline">
                        <button type="submit"><?= e(t('admin.translate.translate')) ?> (EN)</button>
                    </form>
                <?php endif; ?>

                <?php if (empty($artist['has_ja'])): ?>
                    <form method="post" action="<?= url('/admin/translate/artist/' . $artist['id'] . '/ja') ?>" style="display:inline">
                        <button type="submit"><?= e(t('admin.translate.translate')) ?> (JA)</button>
                    </form>
                <?php endif; ?>

                <?php if (!empty($artist['videos'])): ?>
                    <ul style="margin-top: 0.35rem;">
                        <?php foreach ($artist['videos'] as $video): ?>
                            <li>
                                <small>
                                    <a href="<?= url('/videos/' . $video['id']) ?>" target="_blank">
                                        <?= e($video['title'] ?? $video['youtube_id']) ?>
                                    </a>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p><small><?= e(t('admin.translation_review.no_videos')) ?></small></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($totalPages > 1): ?>
        <style>
            .pagination { display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0; }
        </style>
        <nav class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?= url('/admin/translation-review?page=' . ($page - 1)) ?>"><?= e(t('pagination.previous')) ?></a>
            <?php endif; ?>
            <span class="mono"><?= e(t('pagination.page_of')) ?> <?= (int) $page ?> / <?= (int) $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="<?= url('/admin/translation-review?page=' . ($page + 1)) ?>"><?= e(t('pagination.next')) ?></a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
