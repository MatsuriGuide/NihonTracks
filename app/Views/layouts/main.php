<!DOCTYPE html>
<html lang="<?= e(\App\Core\Lang::current()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($GLOBALS['config']['app_name'] ?? 'NihonTracks') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <header class="site-header">
        <a href="<?= url('/') ?>" class="brand">
            <span class="brand-mark">NihonTracks</span>
            <span class="brand-tagline">j-music archive</span>
        </a>

        <nav class="site-nav">
            <a href="<?= url('/artists') ?>"><?= e(t('nav.artists')) ?></a>
            <a href="<?= url('/videos') ?>"><?= e(t('nav.videos')) ?></a>
            <a href="<?= url('/playlists') ?>"><?= e(t('nav.playlists')) ?></a>
        </nav>

        <div class="site-header-side">
            <nav class="lang-switcher">
                <?php foreach (\App\Core\Lang::getSiteLangs() as $code): ?>
                    <a href="<?= url('/lang/' . $code) ?>"
                       class="<?= $code === \App\Core\Lang::current() ? 'is-active' : '' ?>">
                        <?= e(strtoupper($code)) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <nav class="user-nav">
                <?php if (\App\Core\Auth::check()): ?>
                    <span class="user-greeting"><?= e(t('nav.hello')) ?> <?= e(\App\Core\Auth::user()['name']) ?></span>
                    <?php if (in_array(\App\Core\Auth::role(), ['moderator', 'admin'], true)): ?>
                        <a href="<?= url('/admin') ?>"><?= e(t('nav.admin')) ?></a>
                    <?php endif; ?>
                    <a href="<?= url('/logout') ?>"><?= e(t('nav.logout')) ?></a>
                <?php else: ?>
                    <a href="<?= url('/login') ?>"><?= e(t('nav.login')) ?></a>
                    <a href="<?= url('/register') ?>" class="btn btn-small"><?= e(t('nav.register')) ?></a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="site-main">
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <p>
            &copy; <?= date('Y') ?> NihonTracks — <span class="mono">nihontracks.koshiki.art</span>
            &nbsp;·&nbsp;
            <a href="<?= url('/about') ?>">À propos</a>
        </p>
        <p class="mono">
            <?= (int) \App\Models\Video::countPublished() ?> <?= e(t('footer.videos_count')) ?>
            &nbsp;·&nbsp;
            <?= (int) \App\Models\Artist::countApproved() ?> <?= e(t('footer.artists_count')) ?>
        </p>
    </footer>
</body>
</html>
