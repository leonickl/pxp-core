<h1>Log in to <?= config('title') ?></h1>

<?php foreach($errors as $error): ?>
    <div class="notification"><p class="m-0 p-0"><?= $error ?></p></div>
<?php endforeach ?>

<form action="/login" method="post">
    <label for="username" class="required">Username</label>
    <input type="text" id="username" name="username" required>

    <label for="password" class="required">Password</label>
    <input type="password" id="password" name="password" required>

    <button type="submit" class="btn mt-2">Login</button>
</form>
