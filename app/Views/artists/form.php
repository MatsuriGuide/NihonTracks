<h1><?= $mode === 'edit' ? 'Modifier' : 'Ajouter' ?> un artiste</h1>

<?php if (!empty($errors)): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= $mode === 'edit' ? url('/artists/' . $artistId . '/edit') : url('/artists/create') ?>">
    <p>
        <label for="name">Nom</label><br>
        <input type="text" id="name" name="name" value="<?= e($old['name'] ?? '') ?>" required>
    </p>

    <p>
        <label for="type">Type</label><br>
        <select id="type" name="type">
            <?php foreach (['solo' => 'Solo', 'group' => 'Groupe', 'duo' => 'Duo', 'other' => 'Autre'] as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= ($old['type'] ?? 'solo') === $value ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="status">Statut</label><br>
        <select id="status" name="status">
            <?php foreach (['active' => 'Actif', 'disbanded' => 'Séparé', 'hiatus' => 'En pause'] as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= ($old['status'] ?? 'active') === $value ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="start_year">Année de début</label><br>
        <input type="number" id="start_year" name="start_year" value="<?= e((string) ($old['start_year'] ?? '')) ?>">
    </p>

    <p>
        <label for="end_year">Année de fin (si séparé)</label><br>
        <input type="number" id="end_year" name="end_year" value="<?= e((string) ($old['end_year'] ?? '')) ?>">
    </p>

    <p>
        <label for="label">Label</label><br>
        <input type="text" id="label" name="label" value="<?= e($old['label'] ?? '') ?>">
    </p>

    <p>
        <label for="bio">Bio</label><br>
        <textarea id="bio" name="bio" rows="6"><?= e($old['bio'] ?? '') ?></textarea>
    </p>

    <button type="submit"><?= $mode === 'edit' ? 'Enregistrer' : 'Créer' ?></button>
</form>
