<h1>Signalements</h1>

<h2>En attente (<?= count($pending) ?>)</h2>

<?php if (empty($pending)): ?>
    <p>Aucun signalement en attente.</p>
<?php else: ?>
    <?php foreach ($pending as $report): ?>
        <div class="report">
            <p>
                <strong><?= e(ucfirst($report['reportable_type'])) ?></strong> :
                <?= e($report['content_label']) ?>
                <?php if (!empty($report['content_url'])): ?>
                    (<a href="<?= e($report['content_url']) ?>" target="_blank" rel="noopener">voir</a>)
                <?php endif; ?>
            </p>
            <p>
                Motif : <?= e($report['reason']) ?>
                <?php if (!empty($report['comment'])): ?>
                    — « <?= e($report['comment']) ?> »
                <?php endif; ?>
            </p>
            <p>Signalé par <?= e($report['reporter_name']) ?> le <?= e($report['created_at']) ?></p>

            <form method="post" action="<?= url('/admin/reports/' . $report['id'] . '/resolve') ?>" style="display:inline">
                <input type="hidden" name="action" value="dismiss">
                <button type="submit">Rejeter (ignorer)</button>
            </form>

            <?php if ($report['reportable_type'] === 'video'): ?>
                <form method="post" action="<?= url('/admin/reports/' . $report['id'] . '/resolve') ?>" style="display:inline">
                    <input type="hidden" name="action" value="hide">
                    <button type="submit">Masquer la vidéo</button>
                </form>
            <?php endif; ?>

            <form method="post" action="<?= url('/admin/reports/' . $report['id'] . '/resolve') ?>" style="display:inline"
                  onsubmit="return confirm('Supprimer définitivement ce contenu ?');">
                <input type="hidden" name="action" value="delete">
                <button type="submit">Supprimer le contenu</button>
            </form>
        </div>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>

<h2>Historique récent</h2>

<?php if (empty($resolved)): ?>
    <p>Aucun signalement traité pour l'instant.</p>
<?php else: ?>
    <ul>
        <?php foreach ($resolved as $report): ?>
            <li>
                <?= e(ucfirst($report['reportable_type'])) ?> « <?= e($report['content_label']) ?> »
                — <?= e($report['status']) ?>
                par <?= e($report['resolver_name'] ?? '?') ?>
                le <?= e($report['resolved_at']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="<?= url('/admin') ?>">&larr; Retour au tableau de bord</a></p>
