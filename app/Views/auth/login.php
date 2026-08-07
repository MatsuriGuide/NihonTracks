<h1><?= e(t('auth.login.title')) ?></h1>

<?php if (!empty($errors)): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= url('/login') ?>">
    <p>
        <label for="email"><?= e(t('auth.login.email')) ?></label><br>
        <input type="email" id="email" name="email"
               value="<?= e($old['email'] ?? '') ?>" required>
    </p>

    <p>
        <label for="password"><?= e(t('auth.login.password')) ?></label><br>
        <input type="password" id="password" name="password" required>
    </p>

    <button type="submit"><?= e(t('auth.login.submit')) ?></button>
</form>

<p><a href="<?= url('/register') ?>"><?= e(t('auth.login.no_account')) ?></a></p>
