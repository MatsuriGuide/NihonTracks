<h1><?= $mode === 'edit' ? e(t('artists.edit_title')) : e(t('artists.create_title')) ?></h1>

<?php if (!empty($errors)): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= $mode === 'edit' ? url('/artists/' . $artistId . '/edit') : url('/artists/create') ?>">
    <p>
        <label for="name"><?= e(t('artists.name')) ?></label><br>
        <input type="text" id="name" name="name" value="<?= e($old['name'] ?? '') ?>" required>
    </p>

    <p>
        <label for="type"><?= e(t('artists.type')) ?></label><br>
        <select id="type" name="type">
            <?php foreach (['solo', 'group', 'duo', 'other'] as $value): ?>
                <option value="<?= e($value) ?>" <?= ($old['type'] ?? 'solo') === $value ? 'selected' : '' ?>>
                    <?= e(t('artists.type.' . $value)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="status"><?= e(t('artists.status')) ?></label><br>
        <select id="status" name="status">
            <?php foreach (['active', 'disbanded', 'hiatus'] as $value): ?>
                <option value="<?= e($value) ?>" <?= ($old['status'] ?? 'active') === $value ? 'selected' : '' ?>>
                    <?= e(t('artists.status.' . $value)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="start_year"><?= e(t('artists.start_year')) ?></label><br>
        <input type="number" id="start_year" name="start_year" value="<?= e((string) ($old['start_year'] ?? '')) ?>">
    </p>

    <p>
        <label for="end_year"><?= e(t('artists.end_year')) ?></label><br>
        <input type="number" id="end_year" name="end_year" value="<?= e((string) ($old['end_year'] ?? '')) ?>">
    </p>

    <p>
        <label for="label"><?= e(t('artists.label')) ?></label><br>
        <input type="text" id="label" name="label" value="<?= e($old['label'] ?? '') ?>">
    </p>

    <p>
        <label for="bio"><?= e(t('artists.bio')) ?></label><br>
        <textarea id="bio" name="bio" rows="6"><?= e($old['bio'] ?? '') ?></textarea>
    </p>

    <button type="submit"><?= $mode === 'edit' ? e(t('artists.submit_edit')) : e(t('artists.submit_create')) ?></button>
</form>
