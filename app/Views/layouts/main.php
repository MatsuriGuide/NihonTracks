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
            <a href="<?= url('/artists') ?>"><?= e(t('nav.artists')) ?></a>
            <a href="<?= url('/videos') ?>"><?= e(t('nav.videos')) ?></a>
            <a href="<?= url('/playlists') ?>"><?= e(t('nav.playlists')) ?></a>
            <?php if (\App\Core\Auth::check()): ?>
                <span><?= e(t('nav.hello')) ?> <?= e(\App\Core\Auth::user()['name']) ?></span>
                <?php if (in_array(\App\Core\Auth::role(), ['moderator', 'admin'], true)): ?>
                    <a href="<?= url('/admin') ?>"><?= e(t('nav.admin')) ?></a>
                <?php endif; ?>
                <a href="<?= url('/logout') ?>"><?= e(t('nav.logout')) ?></a>
            <?php else: ?>
                <a href="<?= url('/login') ?>"><?= e(t('nav.login')) ?></a>
                <a href="<?= url('/register') ?>"><?= e(t('nav.register')) ?></a>
            <?php endif; ?>
        </nav>
        <nav class="lang-switcher">
            <?php foreach (\App\Core\Lang::getSiteLangs() as $code): ?>
                <a href="<?= url('/lang/' . $code) ?>"
                   <?= $code === \App\Core\Lang::current() ? 'style="font-weight:bold;"' : '' ?>>
                    <?= e(strtoupper($code)) ?>
                </a>
            <?php endforeach; ?>
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
