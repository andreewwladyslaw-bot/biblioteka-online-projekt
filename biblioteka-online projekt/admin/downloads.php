
<?php
// День 10 — журнал скачиваний F07

require_once __DIR__ . '/../config/auth.php';

requireLogin('../login.php');
requireAdmin();

require_once __DIR__ . '/../config/database.php';

// Загружаем историю скачиваний
$stmt = $pdo->query(
    'SELECT
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
     ORDER BY d.downloaded_at DESC, d.id DESC'
);

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
