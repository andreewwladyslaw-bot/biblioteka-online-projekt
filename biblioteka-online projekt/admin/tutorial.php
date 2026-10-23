
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
    <title>Инструкция по библиотеке</title>
    <link rel="stylesheet"
          href="../css/style.css">
</head>
<body>

<main class="container">

    <h1>Как пользоваться библиотекой</h1>

    <p>
        Учебный пример использования изображения
        на веб-странице.
    </p>

    <figure>
        <img
            src="../images/library_instruction.png"
            alt="Иллюстрация этапов поиска и скачивания PDF"
            width="600"
            style="max-width:100%;height:auto;"
        >

        <figcaption>
            Электронная библиотека:
            поиск и скачивание материалов.
        </figcaption>
    </figure>

    <h2>Основные действия</h2>

    <ol>
        <li>Войти в библиотеку.</li>
        <li>Найти материал в каталоге.</li>
        <li>Открыть карточку PDF.</li>
        <li>Нажать «Скачать PDF».</li>
    </ol>

    <p>
        <a href="dashboard.php">
            Вернуться в панель администратора
        </a>
    </p>

</main>

</body>
</html>
