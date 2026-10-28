
<?php
require_once __DIR__ . '/../config/auth.php';

requireLogin('../login.php');
requireAdmin();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <h1>Панель администратора</h1>
    
    <h2>Управление библиотекой</h2>

    <nav>
        <p>        
            <a href="categories.php">
            Управление категориями
            </a>
        </p>
        <p>
            <a href="add_material.php">
            Добавить PDF-материал
            </a>
        </p>

        <p>
            <a href="downloads.php">
            Журнал скачиваний
            </a>
        </p>
        <p>
            <a href="tutorial.php">
                Учебная инструкция
            </a>
        </p>
        <p>
            <a href="../index.php">
            Перейти в каталог
            </a>
        </p>
        
    </nav>
    
<h2>Управление материалами</h2>

<p>
    <a href="add_material.php">
        Добавить PDF-материал
    </a>
</p>

<p>
    <a href="categories.php">
        Управление категориями
    </a>
</p>

<p>
    <a href="../index.php">
        Открыть каталог
    </a>
</p>

<p>
    <a href="materials.php">
        Управление материалами
    </a>
</p>



    <p>
        Здравствуйте,
        <?= esc((string) $_SESSION['full_name']) ?>!
    </p>

    <p>У вас есть права администратора.</p>

   

    <a href="../index.php">
        Вернуться на главную
    </a>

    <form action="../logout.php" method="POST">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= esc(csrfToken()) ?>"
        >

        <button type="submit">Выйти</button>
    </form>

</body>
</html>
