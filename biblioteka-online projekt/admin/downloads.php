
<?php
// День 10 — журнал скачиваний F07

require_once __DIR__ . '/../config/auth.php';

requireLogin('../login.php');
requireAdmin();

require_once __DIR__ . '/../config/database.php';

// 1. Получаем пользователей
$users = $pdo->query(
    'SELECT id, full_name, username
     FROM users
     ORDER BY full_name, id'
)->fetchAll(PDO::FETCH_ASSOC);

// 2. Получаем материалы, включая скрытые
$materials = $pdo->query(
    'SELECT id, title
     FROM materials
     ORDER BY title, id'
)->fetchAll(PDO::FETCH_ASSOC);

// 3. Читаем фильтры из адреса страницы
$userId = null;
$materialId = null;

$userInput = $_GET['user_id'] ?? '';
$materialInput = $_GET['material_id'] ?? '';

// Проверяем ID пользователя
if ($userInput !== '') {

    if (!is_string($userInput)) {
        http_response_code(400);
        exit('Неверный параметр пользователя.');
    }

    $userId = filter_var(
        $userInput,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if (!is_int($userId)) {
        http_response_code(400);
        exit('Неверный ID пользователя.');
    }
}

// Проверяем ID материала
if ($materialInput !== '') {

    if (!is_string($materialInput)) {
        http_response_code(400);
        exit('Неверный параметр материала.');
    }

    $materialId = filter_var(
        $materialInput,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if (!is_int($materialId)) {
        http_response_code(400);
        exit('Неверный ID материала.');
    }
}

// 4. Основной запрос к истории
$sql = '
    SELECT
        d.id,
        d.user_id,
        d.material_id,
        d.file_version,
        d.downloaded_at,
        u.full_name,
        u.username,
        m.title AS material_title
    FROM downloads AS d
    INNER JOIN users AS u
        ON d.user_id = u.id
    INNER JOIN materials AS m
        ON d.material_id = m.id
    WHERE 1 = 1
';

// 5. Добавляем фильтр пользователя
if ($userId !== null) {
    $sql .= ' AND d.user_id = :user_id';
}

// 6. Добавляем фильтр материала
if ($materialId !== null) {
    $sql .= ' AND d.material_id = :material_id';
}

// 7. Новые записи показываем сверху
$sql .= '
    ORDER BY d.downloaded_at DESC, d.id DESC
';

// 8. Подготавливаем SQL
$stmt = $pdo->prepare($sql);

// Передаём выбранные значения
if ($userId !== null) {
    $stmt->bindValue(
        ':user_id',
        $userId,
        PDO::PARAM_INT
    );
}

if ($materialId !== null) {
    $stmt->bindValue(
        ':material_id',
        $materialId,
        PDO::PARAM_INT
    );
}

// 9. Получаем результаты
$stmt->execute();

$downloads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Журнал скачиваний</title>

    <link rel="stylesheet"
          href="../css/style.css">
</head>
<body>

<main class="container">

    <h1>Журнал скачиваний PDF</h1>

    <p>
        <a href="dashboard.php">
            ← Панель администратора
        </a>
    </p>

    <form action="../logout.php" method="POST">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= esc(csrfToken()) ?>"
        >

        <button type="submit">Выйти</button>
    </form>
    <h2>Фильтрация скачиваний</h2>

    <form
        method="GET"
        action="downloads.php"
        class="download-filters"
    >

        <div>
            <label for="user_id">
                Пользователь:
            </label>

            <select
                name="user_id"
                id="user_id"
            >
                <option value="">
                    Все пользователи
                </option>

                <?php foreach ($users as $user): ?>

                    <option
                        value="<?= (int) $user['id'] ?>"
                        <?php if (
                            $userId === (int) $user['id']
                        ): ?>
                            selected
                        <?php endif; ?>
                    >
                        <?= esc((string) $user['full_name']) ?>
                        (<?= esc((string) $user['username']) ?>)
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <div>
            <label for="material_id">
                Материал:
            </label>

            <select
                name="material_id"
                id="material_id"
            >
                <option value="">
                    Все материалы
                </option>

                <?php foreach ($materials as $material): ?>

                    <option
                        value="<?= (int) $material['id'] ?>"
                        <?php if (
                            $materialId ===
                            (int) $material['id']
                        ): ?>
                            selected
                        <?php endif; ?>
                    >
                        <?= esc((string) $material['title']) ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <div>
            <button type="submit">
                Показать
            </button>

            <a href="downloads.php">
                Очистить фильтры
            </a>
        </div>

    </form>
    <h2>История скачиваний</h2>

    <p>
        Всего записей:
        <?= count($downloads) ?>
    </p>

    <?php if (empty($downloads)): ?>

        <p>Скачиваний пока нет.</p>

    <?php else: ?>

        <div class="table-wrapper">
            <table class="downloads-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Материал</th>
                        <th>Версия</th>
                        <th>Дата и время</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($downloads as $download): ?>

                    <tr>
                        <td>
                            <?= (int) $download['id'] ?>
                        </td>

                        <td>
                            <?= esc((string) $download['full_name']) ?>
                            <small>
                                (<?= esc((string) $download['username']) ?>)
                            </small>
                        </td>

                        <td>
                            <?= esc((string) $download['material_title']) ?>
                        </td>

                        <td>
                            <?= (int) $download['file_version'] ?>
                        </td>

                        <td>
                            <?= esc((string) $download['downloaded_at']) ?>
                        </td>
                    </tr>

                <?php endforeach; ?>

                </tbody>
            </table>
        </div>

    <?php endif; ?>

</main>
</body>
</html>
