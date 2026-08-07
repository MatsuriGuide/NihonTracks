<h1><?= $mode === 'edit' ? 'Modifier' : 'Créer' ?> une playlist</h1>

<?php if (!empty($errors)): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= $mode === 'edit' ? url('/playlists/' . $playlistId . '/edit') : url('/playlists/create') ?>">
    <p>
        <label for="name">Nom</label><br>
        <input type="text" id="name" name="name" value="<?= e($old['name'] ?? '') ?>" required>
    </p>

    <p>
        <label for="description">Description</label><br>
        <textarea id="description" name="description" rows="4"><?= e($old['description'] ?? '') ?></textarea>
    </p>

    <p>
        <label>
            <input type="checkbox" name="is_public" value="1" <?= !empty($old['is_public']) ? 'checked' : '' ?>>
            Playlist publique (visible par tous)
        </label>
    </p>

    <button type="submit"><?= $mode === 'edit' ? 'Enregistrer' : 'Créer' ?></button>
</form>
