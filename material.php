
<?php
require_once __DIR__ . '/config/auth.php';
requireLogin();

require_once __DIR__ . '/config/database.php';

// Получаем идентификатор материала из URL
$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!is_int($id)) {
    http_response_code(404);
    exit('Неверный ID материала. Откройте документ из каталога.');
}

// Ищем материал, только если он видимый
$stmt = $pdo->prepare(
    'SELECT m.id, m.title, m.author,
            m.description, m.version,
            c.name AS category_name
     FROM materials AS m
     INNER JOIN categories AS c
         ON c.id = m.category_id
     WHERE m.id = :id AND m.is_visible = 1
     LIMIT 1'
);

$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$material = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$material) {
    http_response_code(404);
    exit('Материал не найден или скрыт.');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>
        <?= esc((string) $material['title']) ?>
    </title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<main class="container">

    <p>
        <a href="index.php">← Вернуться в каталог</a>
    </p>

    <article class="card">
        <h1>
            <?= esc((string) $material['title']) ?>
        </h1>

        <p>
            <strong>Автор:</strong>
            <?= esc((string) $material['author']) ?>
        </p>

        <p>
            <strong>Категория:</strong>
            <?= esc((string) $material['category_name']) ?>
        </p>

        <p>
            <strong>Версия:</strong>
            <?= (int) $material['version'] ?>
        </p>

        <h2>Описание</h2>

        <p>
            <?= nl2br(esc((string) $material['description'])) ?>
        </p>

        
        <a
        href="download.php?id=<?= (int) $material['id'] ?>"
        class="download-button"
        >
        Скачать PDF
        </a>


        
    </article>

</main>
</body>
</html>
