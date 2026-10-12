
<?php
require_once __DIR__ . '/config/auth.php';

// Без входа страница недоступна
requireLogin();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Электронная библиотека</title>
</head>
<body>

    <h1>Электронная библиотека компании</h1>

    <p>
        Добро пожаловать,
        <?= esc((string) $_SESSION['full_name']) ?>!
    </p>

    <p>
        Ваша роль:
        <?= esc((string) $_SESSION['role']) ?>
    </p>

    <p>Каталог материалов будет добавлен позже.</p>

    <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="admin/dashboard.php">
            Панель администратора
        </a>
    <?php endif; ?>

    <form action="logout.php" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= esc(csrfToken()) ?>"
        >

        <button type="submit">Выйти</button>
    </form>

</body>
</html>

