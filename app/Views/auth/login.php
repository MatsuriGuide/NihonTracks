<h1>Connexion</h1>

<?php if (!empty($errors)): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= url('/login') ?>">
    <p>
        <label for="email">Email</label><br>
        <input type="email" id="email" name="email"
               value="<?= e($old['email'] ?? '') ?>" required>
    </p>

    <p>
        <label for="password">Mot de passe</label><br>
        <input type="password" id="password" name="password" required>
    </p>

    <button type="submit">Se connecter</button>
</form>

<p><a href="<?= url('/register') ?>">Créer un compte</a></p>
