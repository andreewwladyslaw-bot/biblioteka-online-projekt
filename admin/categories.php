
<?php
require_once __DIR__ . '/../config/auth.php';
requireLogin('../login.php');
requireAdmin();

require_once __DIR__ . '/../config/database.php';

$error = '';

$messages = [
    'added' => 'Категория успешно добавлена.',
    'renamed' => 'Категория переименована.',
    'deleted' => 'Категория удалена.'
];

$status = is_string($_GET['status'] ?? null)
    ? $_GET['status']
    : '';

$message = $messages[$status] ?? '';

// Обрабатываем формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? null;

    if (
        !is_string($token) ||
        !hash_equals(csrfToken(), $token)
    ) {
        http_response_code(403);
        exit('Недействительный запрос.');
    }

    $action = $_POST['action'] ?? '';

    if (!is_string($action)) {
        $action = '';
    }

    $name = $_POST['name'] ?? '';

    if (!is_string($name)) {
        $name = '';
    }

    $name = trim($name);

    $id = filter_var(
        $_POST['id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    try {

        // Добавление категории
        if ($action === 'add') {

            if ($name === '' ||
                mb_strlen($name, 'UTF-8') > 100) {
                $error = 'Введите название до 100 символов.';
            } else {

                $stmt = $pdo->prepare(
                    'INSERT INTO categories (name)
                     VALUES (:name)'
                );

                $stmt->execute([
                    'name' => $name
                ]);

                header('Location: categories.php?status=added');
                exit;
            }

        // Переименование категории
        } elseif ($action === 'rename') {

            if (!is_int($id)) {
                $error = 'Неверный ID категории.';
            } elseif (
                $name === '' ||
                mb_strlen($name, 'UTF-8') > 100
            ) {
                $error = 'Введите название до 100 символов.';
            } else {

                $stmt = $pdo->prepare(
                    'UPDATE categories
                     SET name = :name
                     WHERE id = :id'
                );

                $stmt->execute([
                    'name' => $name,
                    'id' => $id
                ]);

                header('Location: categories.php?status=renamed');
                exit;
            }

        // Удаление пустой категории
        } elseif ($action === 'delete') {

            if (!is_int($id)) {
                $error = 'Неверный ID категории.';
            } else {

                // Считаем связанные материалы
                $stmt = $pdo->prepare(
                    'SELECT COUNT(*)
                     FROM materials
                     WHERE category_id = :id'
                );

                $stmt->execute(['id' => $id]);

                $count = (int) $stmt->fetchColumn();

                if ($count > 0) {

                    $error = 'Нельзя удалить категорию с материалами.';

                } else {

                    $stmt = $pdo->prepare(
                        'DELETE FROM categories
                         WHERE id = :id'
                    );

                    $stmt->execute(['id' => $id]);

                    header(
                        'Location: categories.php?status=deleted'
                    );
                    exit;
                }
            }

        } else {
            $error = 'Неизвестное действие.';
        }

    } catch (PDOException $e) {

        error_log($e->getMessage());

        if ($e->getCode() === '23000') {
            $error = 'Категория уже существует или '
                . 'связана с материалами.';
        } else {
            $error = 'Ошибка работы с базой данных.';
        }
    }
}

// Загружаем список категорий
$stmt = $pdo->query(
    'SELECT
        c.id,
        c.name,
        COUNT(m.id) AS materials_count
     FROM categories AS c
     LEFT JOIN materials AS m
         ON m.category_id = c.id
     GROUP BY c.id, c.name
     ORDER BY c.name ASC'
);

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Управление категориями</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<main class="container">

    <h1>Управление категориями</h1>

    <p>
        <a href="dashboard.php">
            Панель администратора
        </a>
        |
        <a href="../index.php">
            Каталог
        </a>
    </p>

    <form method="POST" action="../logout.php">
        <input type="hidden"
               name="csrf_token"
               value="<?= esc(csrfToken()) ?>">
        <button type="submit">Выйти</button>
    </form>

    <?php if ($message !== ''): ?>
        <p class="success">
            <?= esc($message) ?>
        </p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p class="error">
            <?= esc($error) ?>
        </p>
    <?php endif; ?>

    <h2>Добавить категорию</h2>

    <form method="POST" action="categories.php">
        <input type="hidden"
               name="action"
               value="add">

        <input type="hidden"
               name="csrf_token"
               value="<?= esc(csrfToken()) ?>">

        <label for="name">Название категории:</label>

        <input
            type="text"
            id="name"
            name="name"
            maxlength="100"
            required
        >

        <button type="submit">Добавить</button>
    </form>

    <h2>Список категорий</h2>

    <?php if (empty($categories)): ?>
        <p>Категорий пока нет.</p>
    <?php else: ?>

        <div class="category-list">

        <?php foreach ($categories as $category): ?>

            <section class="card">

                <h3>
                    <?= esc((string) $category['name']) ?>
                </h3>

                <p>
                    Материалов:
                    <?= (int) $category['materials_count'] ?>
                </p>

                <h4>Переименовать</h4>

                <form method="POST"
                      action="categories.php">

                    <input type="hidden"
                           name="action"
                           value="rename">

                    <input type="hidden"
                           name="id"
                           value="<?= (int) $category['id'] ?>">

                    <input type="hidden"
                           name="csrf_token"
                           value="<?= esc(csrfToken()) ?>">

                    <input
                        type="text"
                        name="name"
                        value="<?= esc((string) $category['name']) ?>"
                        maxlength="100"
                        required
                    >

                    <button type="submit">
                        Сохранить
                    </button>
                </form>

                <h4>Удалить категорию</h4>

                <form method="POST"
                      action="categories.php">

                    <input type="hidden"
                           name="action"
                           value="delete">

                    <input type="hidden"
                           name="id"
                           value="<?= (int) $category['id'] ?>">

                    <input type="hidden"
                           name="csrf_token"
                           value="<?= esc(csrfToken()) ?>">

                    <?php if (
                        (int) $category['materials_count'] > 0
                    ): ?>

                        <button type="submit" disabled>
                            Удаление запрещено
                        </button>

                    <?php else: ?>

                        <button type="submit">
                            Удалить
                        </button>

                    <?php endif; ?>

                </form>

            </section>

        <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>
</body>
</html>
