<h1>Administration</h1>

<p>Signalements en attente : <strong><?= (int) $pendingReports ?></strong></p>

<ul>
    <li><a href="<?= url('/admin/reports') ?>">Traiter les signalements</a></li>
    <li><a href="<?= url('/admin/tags') ?>">Gérer les tags</a></li>
</ul>
