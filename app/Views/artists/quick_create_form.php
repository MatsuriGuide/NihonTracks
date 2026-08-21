<h1><?= e(t('artists.quick_create.title')) ?></h1>

<?php if (!empty($errors)): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p class="hint hint-success"><?= e(t('artists.quick_create.channel_found')) ?> <code><?= e($canonicalUrl) ?></code></p>

<form method="post" action="<?= url('/artists/quick-create/store') ?>">
    <input type="hidden" name="channel_id" value="<?= e($channelId) ?>">
    <input type="hidden" name="canonical_url" value="<?= e($canonicalUrl) ?>">

    <p>
        <label for="name"><?= e(t('artists.name')) ?></label><br>
        <input type="text" id="name" name="name" value="<?= e($suggestedName) ?>" required
               style="width: 100%; max-width: 500px;">
    </p>

    <p>
        <label for="type"><?= e(t('artists.type')) ?></label><br>
        <select id="type" name="type">
            <?php foreach (['solo', 'group', 'duo', 'other'] as $value): ?>
                <option value="<?= e($value) ?>"><?= e(t('artists.type.' . $value)) ?></option>
            <?php endforeach; ?>
        </select>
    </p>

    <button type="submit"><?= e(t('artists.quick_create.confirm')) ?></button>
</form>
