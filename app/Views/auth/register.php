<h1><?= e(t('auth.register.title')) ?></h1>

<?php if (!empty($errors)): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= url('/register') ?>">
    <p>
        <label for="display_name"><?= e(t('auth.register.name')) ?></label><br>
        <input type="text" id="display_name" name="display_name"
               value="<?= e($old['display_name'] ?? '') ?>" required>
    </p>

    <p>
        <label for="email"><?= e(t('auth.register.email')) ?></label><br>
        <input type="email" id="email" name="email"
               value="<?= e($old['email'] ?? '') ?>" required>
    </p>

    <p>
        <label for="password"><?= e(t('auth.register.password')) ?></label><br>
        <input type="password" id="password" name="password" required minlength="8">
    </p>

    <p>
        <label for="password_confirm"><?= e(t('auth.register.password_confirm')) ?></label><br>
        <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
    </p>

    <button type="submit"><?= e(t('auth.register.submit')) ?></button>
</form>

<p><a href="<?= url('/login') ?>"><?= e(t('auth.register.have_account')) ?></a></p>
