<h1><?= e($playlist['name']) ?></h1>

<?php if (!empty($playlist['description'])): ?>
    <p><?= nl2br(e($playlist['description'])) ?></p>
<?php endif; ?>

<p><?= $playlist['is_public'] ? 'Playlist publique' : 'Playlist privée' ?></p>

<?php if (\App\Core\Auth::canEdit((int) $playlist['user_id'])): ?>
    <p>
        <a href="<?= url('/playlists/' . $playlist['id'] . '/edit') ?>">Modifier</a>
        &nbsp;
        <form method="post" action="<?= url('/playlists/' . $playlist['id'] . '/delete') ?>"
              onsubmit="return confirm('Supprimer cette playlist ?');" style="display:inline">
            <button type="submit">Supprimer</button>
        </form>
    </p>
<?php endif; ?>

<h2>Vidéos</h2>

<?php if (empty($videos)): ?>
    <p>Aucune vidéo dans cette playlist.</p>
<?php else: ?>
    <ol>
        <?php foreach ($videos as $video): ?>
            <li>
                <a href="<?= url('/videos/' . $video['id']) ?>"><?= e($video['title'] ?? $video['youtube_id']) ?></a>
                <?php if (!empty($video['artist_names'])): ?>
                    — <?= e($video['artist_names']) ?>
                <?php endif; ?>
                <?php if (\App\Core\Auth::canEdit((int) $playlist['user_id'])): ?>
                    <form method="post"
                          action="<?= url('/playlists/' . $playlist['id'] . '/videos/' . $video['id'] . '/remove') ?>"
                          style="display:inline">
                        <button type="submit">Retirer</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>

<?php if (\App\Core\Auth::canEdit((int) $playlist['user_id']) && !empty($availableVideos)): ?>
    <h2>Ajouter une vidéo</h2>
    <form method="post" action="<?= url('/playlists/' . $playlist['id'] . '/videos') ?>">
        <select name="video_id" required>
            <option value="">-- Choisir une vidéo --</option>
            <?php foreach ($availableVideos as $video): ?>
                <option value="<?= (int) $video['id'] ?>"><?= e($video['title'] ?? $video['youtube_id']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Ajouter</button>
    </form>
<?php endif; ?>

<p><a href="<?= url('/playlists') ?>">&larr; Retour aux playlists</a></p>
