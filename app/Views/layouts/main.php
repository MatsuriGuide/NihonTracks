<!DOCTYPE html>
<html lang="<?= e(\App\Core\Lang::current()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($GLOBALS['config']['app_name'] ?? 'NihonTracks') ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <header>
        <a href="<?= url('/') ?>">NihonTracks</a>
        <nav>
            <?php if (\App\Core\Auth::check()): ?>
                <span>Bonjour, <?= e(\App\Core\Auth::user()['name']) ?></span>
                <?php if (in_array(\App\Core\Auth::role(), ['moderator', 'admin'], true)): ?>
                    <a href="<?= url('/admin') ?>">Administration</a>
                <?php endif; ?>
                <a href="<?= url('/logout') ?>">Déconnexion</a>
            <?php else: ?>
                <a href="<?= url('/login') ?>">Connexion</a>
                <a href="<?= url('/register') ?>">Créer un compte</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> NihonTracks</p>
    </footer>
</body>
</html>
