<?php
require_once __DIR__ . '/../config/auth.php';
requireLogin('../login.php');
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$stmt = $pdo->query(
    'SELECT m.id, m.title, m.author, m.version, m.is_visible,
            c.name AS category_name
     FROM materials AS m
     INNER JOIN categories AS c ON c.id = m.category_id
     ORDER BY m.id DESC'
);
$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie materiałami</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<main class="container">
    <h1>Zarządzanie materiałami</h1>
    <p><a href="dashboard.php">← Panel administratora</a></p>
    <p><a href="add_material.php">Dodaj PDF</a></p>
    <?php if (!$materials): ?>
        <p>Brak materiałów.</p>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead><tr><th>ID</th><th>Tytuł</th><th>Autor</th><th>Kategoria</th><th>Wersja</th><th>Status</th><th>Działanie</th></tr></thead>
            <tbody>
                <?php foreach ($materials as $m): ?>
                    <tr>
                        <td><?= (int) $m['id'] ?></td>
                        <td><?= esc((string) $m['title']) ?></td>
                        <td><?= esc((string) $m['author']) ?></td>
                        <td><?= esc((string) $m['category_name']) ?></td>
                        <td><?= (int) $m['version'] ?></td>
                        <td><?= (int) $m['is_visible'] === 1 ? 'Widoczny' : 'Ukryty' ?></td>
                        <td><a href="edit_material.php?id=<?= (int) $m['id'] ?>">Edytuj</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
