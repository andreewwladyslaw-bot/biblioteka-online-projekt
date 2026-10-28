<?php
require_once __DIR__ . '/../config/auth.php';
requireLogin('../login.php');
requireAdmin();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<main class="container">
    <h1>Панель администратора</h1>
    <p>Здравствуйте, <?= esc((string) ($_SESSION['full_name'] ?? 'Администратор')) ?>!</p>
    <h2>Управление библиотекой</h2>
    <nav aria-label="Разделы управления">
        <ul>
            <li><a href="materials.php">Управление материалами</a></li>
            <li><a href="add_material.php">Добавить PDF-материал</a></li>
            <li><a href="categories.php">Управление категориями</a></li>
            <li><a href="downloads.php">Журнал скачиваний</a></li>
            <li><a href="tutorial.php">Учебная инструкция</a></li>
            <li><a href="../index.php">Вернуться в каталог</a></li>
        </ul>
    </nav>
    <form action="../logout.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= esc(csrfToken()) ?>">
        <button type="submit">Выйти</button>
    </form>
</main>
</body>
</html>
