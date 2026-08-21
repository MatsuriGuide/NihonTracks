<h1><?= e(t('artists.quick_create.title')) ?></h1>

<?php if (!empty($error)): ?>
    <p class="errors">
        <?= e($error) ?>
        <?php if (!empty($existingArtistSlug)): ?>
            — <a href="<?= url('/artists/' . $existingArtistSlug) ?>"><?= e(t('artists.quick_create.view_existing')) ?></a>
        <?php endif; ?>
    </p>
<?php endif; ?>

<form method="post" action="<?= url('/artists/quick-create/preview') ?>">
    <p>
        <label for="channel_url"><?= e(t('artists.quick_create.url_label')) ?></label><br>
        <input type="url" id="channel_url" name="channel_url"
               placeholder="https://www.youtube.com/channel/... ou https://www.youtube.com/@..." required
               style="width: 100%; max-width: 500px;">
    </p>
    <p><small><?= e(t('artists.quick_create.url_hint')) ?></small></p>
    <button type="submit"><?= e(t('artists.quick_create.submit')) ?></button>
</form>

<p><a href="<?= url('/artists/create') ?>"><?= e(t('artists.quick_create.use_classic_form')) ?></a></p>
