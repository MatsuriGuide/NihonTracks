<h1><?= e(t('admin.tags_title')) ?></h1>

<?php foreach ($categories as $category): ?>
    <h2><?= e($category['slug']) ?></h2>
    <ul>
        <?php $hasAny = false; ?>
        <?php foreach ($tags as $tag): ?>
            <?php if ((int) $tag['category_id'] === (int) $category['id']): ?>
                <?php $hasAny = true; ?>
                <li>
                    <?= e($tag['name'] ?? $tag['slug']) ?>
                    <form method="post" action="<?= url('/admin/tags/' . $tag['id'] . '/delete') ?>"
                          onsubmit="return confirm('<?= e(t('admin.tags.delete_confirm')) ?>');" style="display:inline">
                        <button type="submit"><?= e(t('admin.tags.delete')) ?></button>
                    </form>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if (!$hasAny): ?>
            <li><?= e(t('admin.tags.empty_category')) ?></li>
        <?php endif; ?>
    </ul>
<?php endforeach; ?>

<details>
    <summary><?= e(t('admin.tags.add_title')) ?></summary>
    <form method="post" action="<?= url('/admin/tags') ?>">
        <p>
            <label for="category_id"><?= e(t('admin.tags.category_label')) ?></label><br>
            <select id="category_id" name="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>"><?= e($category['slug']) ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="name_fr"><?= e(t('admin.tags.name_fr')) ?></label><br>
            <input type="text" id="name_fr" name="name_fr" required>
        </p>
        <p>
            <label for="name_en"><?= e(t('admin.tags.name_en')) ?></label><br>
            <input type="text" id="name_en" name="name_en">
        </p>
        <p>
            <label for="name_ja"><?= e(t('admin.tags.name_ja')) ?></label><br>
            <input type="text" id="name_ja" name="name_ja">
        </p>
        <p><small><?= e(t('admin.tags.add_hint')) ?></small></p>
        <button type="submit"><?= e(t('admin.tags.submit')) ?></button>
    </form>
</details>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
