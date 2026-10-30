<?php
require_once __DIR__ . '/config/auth.php';
requireLogin();

require_once __DIR__ . '/config/database.php';

// 1. Pobieramy tekst wyszukiwania
$search = '';

if (isset($_GET['q']) && is_string($_GET['q'])) {
    $search = trim($_GET['q']);
}

// 2. Pobieramy wybraną kategorię
$categoryId = null;

if (
    isset($_GET['category_id']) &&
    is_string($_GET['category_id'])
) {
    $value = filter_var(
        $_GET['category_id'],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($value !== false) {
        $categoryId = $value;
    }
}

// 3. Pobieramy listę kategorii
$categories = $pdo->query(
    'SELECT id, name
     FROM categories
     ORDER BY name ASC'
)->fetchAll(PDO::FETCH_ASSOC);

// 4. Główne zapytanie SQL
$sql = '
    SELECT
        m.id,
        m.title,
        m.author,
        m.description,
        c.name AS category_name
    FROM materials AS m
    INNER JOIN categories AS c
        ON m.category_id = c.id
    WHERE m.is_visible = 1
';

// 5. Wyszukiwanie według tytułu lub autora
if ($search !== '') {
    $sql .= '
        AND (
            m.title LIKE :title
            OR m.author LIKE :author
        )
    ';
}

// 6. Filtrowanie według kategorii
if ($categoryId !== null) {
    $sql .= ' AND m.category_id = :category_id';
}

// 7. Sortowanie materiałów
$sql .= ' ORDER BY m.id DESC';

// 8. Przygotowanie zapytania SQL
$stmt = $pdo->prepare($sql);

// 9. Przekazanie parametrów
if ($search !== '') {
    $like = '%' . $search . '%';

    $stmt->bindValue(
        ':title',
        $like,
        PDO::PARAM_STR
    );

    $stmt->bindValue(
        ':author',
        $like,
        PDO::PARAM_STR
    );
}

if ($categoryId !== null) {
    $stmt->bindValue(
        ':category_id',
        $categoryId,
        PDO::PARAM_INT
    );
}

// 10. Wykonanie zapytania
$stmt->execute();

$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Katalog — Biblioteka elektroniczna</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<main class="container">

    <header class="site-header">
        <div>
            <h1>Biblioteka elektroniczna</h1>
            <p>
                Witaj,
                <?= esc((string) $_SESSION['full_name']) ?>
            </p>
        </div>

        <form action="logout.php" method="POST">
            <input type="hidden"
                   name="csrf_token"
                   value="<?= esc(csrfToken()) ?>">

            <button type="submit">Wyloguj się</button>
        </form>
    </header>

    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <p>
            <a href="admin/dashboard.php">
                Panel administratora
            </a>
        </p>
    <?php endif; ?>

    <h2>Katalog materiałów</h2>

    <!-- Formularz wyszukiwania -->
    <form method="GET"
          action="index.php"
          class="filters">

        <div class="filter-group">
            <label for="q">
                Wyszukiwanie według tytułu lub autora
            </label>

            <input
                type="text"
                name="q"
                id="q"
                value="<?= esc($search) ?>"
                placeholder="Wpisz tytuł lub autora"
            >
        </div>

        <div class="filter-group">
            <label for="category_id">
                Kategoria
            </label>

            <select name="category_id"
                    id="category_id">

                <option value="">
                    Wszystkie kategorie
                </option>

                <?php foreach ($categories as $category): ?>
                    <option
                        value="<?= (int) $category['id'] ?>"
                        <?php
                        if (
                            $categoryId ===
                            (int) $category['id']
                        ) {
                            echo 'selected';
                        }
                        ?>
                    >
                        <?= esc((string) $category['name']) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <div class="filter-actions">
            <button type="submit">
                Szukaj
            </button>

            <a href="index.php" class="reset-link">
                Wyczyść wyszukiwanie
            </a>
        </div>
    </form>

    <!-- Wyniki wyszukiwania -->
    <h2>Znalezione materiały</h2>

    <?php if (empty($materials)): ?>

        <p>Nie znaleziono żadnych materiałów</p>

    <?php else: ?>

        <p>
            Liczba znalezionych materiałów:
            <?= count($materials) ?>
        </p>

        <div class="catalog-grid">

            <?php foreach ($materials as $material): ?>

                <article class="card">

                    <h3>
                        <?= esc((string) $material['title']) ?>
                    </h3>

                    <p>
                        <strong>Autor:</strong>
                        <?= esc((string) $material['author']) ?>
                    </p>

                    <p>
                        <strong>Kategoria:</strong>
                        <?= esc((string) $material['category_name']) ?>
                    </p>

                    <p class="description">
                        <?= esc((string) $material['description']) ?>
                    </p>

                    <a href="material.php?id=<?= (int) $material['id'] ?>">
                        Otwórz materiał
                    </a>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>
</body>
</html>

