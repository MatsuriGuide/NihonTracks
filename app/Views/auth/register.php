<h1>Créer un compte</h1>

<?php if (!empty($errors)): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= url('/register') ?>">
    <p>
        <label for="display_name">Nom affiché</label><br>
        <input type="text" id="display_name" name="display_name"
               value="<?= e($old['display_name'] ?? '') ?>" required>
    </p>

    <p>
        <label for="email">Email</label><br>
        <input type="email" id="email" name="email"
               value="<?= e($old['email'] ?? '') ?>" required>
    </p>

    <p>
        <label for="password">Mot de passe (8 caractères minimum)</label><br>
        <input type="password" id="password" name="password" required minlength="8">
    </p>

    <p>
        <label for="password_confirm">Confirmer le mot de passe</label><br>
        <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
    </p>

    <button type="submit">Créer mon compte</button>
</form>

<p><a href="<?= url('/login') ?>">J'ai déjà un compte</a></p>
