<?php
require_once __DIR__ . '/../config/auth.php';
requireLogin('../login.php');
requireAdmin();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Instrukcja obsługi biblioteki</title>
    <link rel="stylesheet"
          href="../css/style.css">
</head>
<body>

<main class="container">

    <h1>Jak korzystać z biblioteki</h1>

    <p>
        Przykład edukacyjnego wykorzystania ilustracji
        na stronie internetowej.
    </p>

    <figure>
        <img
            src="../images/library_instruction.png"
            alt="Ilustracja wyszukiwania i pobierania dokumentów PDF"
            width="600"
            style="max-width:100%;height:auto;"
        >

        <figcaption>
            Biblioteka elektroniczna:
            wyszukiwanie i pobieranie materiałów.
        </figcaption>
    </figure>

    <h2>Podstawowe czynności</h2>

    <ol>
        <li>Zaloguj się do biblioteki.</li>
        <li>Znajdź materiał w katalogu.</li>
        <li>Otwórz stronę materiału PDF.</li>
        <li>Kliknij „Pobierz PDF”.</li>
    </ol>

    <p>
        <a href="dashboard.php">
            Wróć do panelu administratora
        </a>
    </p>

</main>

</body>
</html>
