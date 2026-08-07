<h1><?= $mode === 'edit' ? e(t('playlists.form_edit')) : e(t('playlists.form_create')) ?></h1>

<?php if (!empty($errors)): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= $mode === 'edit' ? url('/playlists/' . $playlistId . '/edit') : url('/playlists/create') ?>">
    <p>
        <label for="name"><?= e(t('playlists.name')) ?></label><br>
        <input type="text" id="name" name="name" value="<?= e($old['name'] ?? '') ?>" required>
    </p>

    <p>
        <label for="description"><?= e(t('playlists.description')) ?></label><br>
        <textarea id="description" name="description" rows="4"><?= e($old['description'] ?? '') ?></textarea>
    </p>

    <p>
        <label>
            <input type="checkbox" name="is_public" value="1" <?= !empty($old['is_public']) ? 'checked' : '' ?>>
            <?= e(t('playlists.is_public_label')) ?>
        </label>
    </p>

    <button type="submit"><?= $mode === 'edit' ? e(t('playlists.submit_edit')) : e(t('playlists.submit_create')) ?></button>
</form>
