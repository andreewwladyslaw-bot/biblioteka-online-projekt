<?php
require_once __DIR__ . '/../config/auth.php';
requireLogin('../login.php');
requireAdmin();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administratora — Biblioteka elektroniczna</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<main class="container">
    <header class="site-header">
        <div>
            <h1>Panel administratora</h1>
            <p>Witaj, <?= esc((string) $_SESSION['full_name']) ?>!</p>
            <p>Masz uprawnienia administratora.</p>
        </div>
        <form action="../logout.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= esc(csrfToken()) ?>">
            <button type="submit">Wyloguj się</button>
        </form>
    </header>

    <h2>Zarządzanie biblioteką</h2>
    <nav aria-label="Menu administratora">
        <div class="catalog-grid">
            <p class="card"><a href="materials.php">Zarządzanie materiałami</a></p>
            <p class="card"><a href="add_material.php">Dodaj materiał PDF</a></p>
            <p class="card"><a href="categories.php">Zarządzanie kategoriami</a></p>
            <p class="card"><a href="downloads.php">Rejestr pobrań</a></p>
            <p class="card"><a href="tutorial.php">Instrukcja dla użytkownika</a></p>
            <p class="card"><a href="../index.php">Przejdź do katalogu</a></p>
        </div>
    </nav>
</main>
</body>
</html>
