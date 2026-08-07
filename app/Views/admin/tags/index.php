<h1><?= e(t('admin.tags_title')) ?></h1>

<?php foreach ($categories as $category): ?>
    <h2><?= e($category['slug']) ?></h2>
    <ul>
        <?php foreach ($tags as $tag): ?>
            <?php if ((int) $tag['category_id'] === (int) $category['id']): ?>
                <li><?= e($tag['name'] ?? $tag['slug']) ?></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
<?php endforeach; ?>
