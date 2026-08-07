<h1>Ajouter une vidéo</h1>

<?php if (!empty($error)): ?>
    <p class="errors"><?= e($error) ?></p>
<?php endif; ?>

<form method="post" action="<?= url('/videos/preview') ?>">
    <p>
        <label for="youtube_url">URL YouTube</label><br>
        <input type="url" id="youtube_url" name="youtube_url"
               placeholder="https://www.youtube.com/watch?v=..." required
               style="width: 100%; max-width: 500px;">
    </p>
    <button type="submit">Récupérer les infos</button>
</form>
