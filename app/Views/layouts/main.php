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
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> NihonTracks</p>
    </footer>
</body>
</html>
