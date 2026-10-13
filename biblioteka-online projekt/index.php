
<?php
require_once __DIR__ . '/config/auth.php';

// Без входа страница недоступна
requireLogin();
require_once __DIR__ . '/config/database.php';
$stmt = $pdo->query(
    'SELECT m.id, m.title, m.author, m.description,
            c.name AS category_name
     FROM materials AS m
     INNER JOIN categories AS c
         ON c.id = m.category_id
     WHERE m.is_visible = 1
     ORDER BY m.id DESC'
);

$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Каталог — Электронная библиотека</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<main class="container">

    <header class="site-header">
        <div>
            <h1>Электронная библиотека компании</h1>
            <p>
                Здравствуйте,
                <?= esc((string) $_SESSION['full_name']) ?>!
            </p>
        </div>

        <form action="logout.php" method="post">
            <input type="hidden"
                   name="csrf_token"
                   value="<?= esc(csrfToken()) ?>">
            <button type="submit">Выйти</button>
        </form>
    </header>

    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <p>
            <a href="admin/dashboard.php">
                Панель администратора
            </a>
        </p>
    <?php endif; ?>

    <h2>Каталог материалов</h2>

    <?php if (count($materials) === 0): ?>
        <p>В каталоге пока нет доступных материалов.</p>
    <?php else: ?>

        <div class="catalog-grid">

            <?php foreach ($materials as $material): ?>
                <article class="card">

                    <h3>
                        <?= esc((string) $material['title']) ?>
                    </h3>

                    <p>
                        <strong>Автор:</strong>
                        <?= esc((string) $material['author']) ?>
                    </p>

                    <p>
                        <strong>Категория:</strong>
                        <?= esc((string) $material['category_name']) ?>
                    </p>

                    <p class="description">
                        <?= esc((string) $material['description']) ?>
                    </p>

                    <a href="material.php?id=<?= (int) $material['id'] ?>">
                        Открыть материал
                    </a>

                </article>
            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>
</body>
</html>

