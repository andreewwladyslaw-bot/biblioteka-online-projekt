<?php
require_once __DIR__ . '/../config/auth.php';

requireLogin('../login.php');
requireAdmin();

require_once __DIR__ . '/../config/database.php';

$error = '';
$maxSize = 20 * 1024 * 1024; // 20 MB

// Folder poza publicznym katalogiem htdocs
$storageDir = dirname($_SERVER['DOCUMENT_ROOT'])
    . DIRECTORY_SEPARATOR . 'biblioteka_private_pdfs';

// Pobieramy kategorie z MySQL
$stmt = $pdo->query(
    'SELECT id, name
     FROM categories
     ORDER BY name'
);

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obsługa formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Weryfikacja tokenu CSRF
    $token = $_POST['csrf_token'] ?? null;

    if (
        !is_string($token) ||
        !hash_equals(csrfToken(), $token)
    ) {
        http_response_code(403);
        exit('Nieprawidłowe żądanie.');
    }

    // 2. Pobieranie danych
    $title = is_string($_POST['title'] ?? null)
        ? trim($_POST['title'])
        : '';

    $author = is_string($_POST['author'] ?? null)
        ? trim($_POST['author'])
        : '';

    $description = is_string($_POST['description'] ?? null)
        ? trim($_POST['description'])
        : '';

    $categoryId = filter_var(
        $_POST['category_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    // 3. Kontrola wymaganych pól
    if (
        $title === '' ||
        $author === '' ||
        $description === '' ||
        !is_int($categoryId)
    ) {
        $error = 'Wypełnij wszystkie wymagane pola.';

    } elseif (
        mb_strlen($title) > 200 ||
        mb_strlen($author) > 150 ||
        mb_strlen($description) > 5000
    ) {
        $error = 'Przekroczono dozwoloną długość tekstu.';

    } elseif (
        !isset($_FILES['pdf']) ||
        !is_array($_FILES['pdf']) ||
        !isset($_FILES['pdf']['error']) ||
        !is_int($_FILES['pdf']['error'])
    ) {
        $error = 'Wybierz plik PDF.';

    } else {

        $file = $_FILES['pdf'];

        // 4. Obsługa błędów przesyłania pliku
        if ($file['error'] !== UPLOAD_ERR_OK) {

            $error = 'Błąd przesyłania pliku. '
                . 'Sprawdź rozmiar pliku i konfigurację PHP.';

        } elseif (
            !isset($file['size'], $file['tmp_name'],
                    $file['name']) ||
            !is_int($file['size']) ||
            !is_string($file['tmp_name']) ||
            !is_string($file['name']) ||
            $file['size'] <= 0 ||
            $file['size'] > $maxSize
        ) {
            $error = 'Plik PDF nie może przekraczać 20 MB.';

        } elseif (
            strtolower(pathinfo(
                $file['name'],
                PATHINFO_EXTENSION
            )) !== 'pdf'
        ) {
            $error = 'Dozwolony jest wyłącznie format PDF.';

        } elseif (!is_uploaded_file($file['tmp_name'])) {

            $error = 'Przesłany plik jest nieprawidłowy.';

        } else {

            // 5. Sprawdzenie typu MIME i nagłówka PDF
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            $handle = fopen($file['tmp_name'], 'rb');
            $signature = $handle
                ? fread($handle, 5)
                : '';

            if ($handle) {
                fclose($handle);
            }

            if (
                $mime !== 'application/pdf' ||
                $signature !== '%PDF-'
            ) {
                $error = 'Plik nie jest poprawnym dokumentem PDF.';
            } else {

                // 6. Sprawdzenie istnienia kategorii
                $stmt = $pdo->prepare(
                    'SELECT COUNT(*)
                     FROM categories
                     WHERE id = :id'
                );

                $stmt->execute([
                    'id' => $categoryId
                ]);

                if ((int) $stmt->fetchColumn() === 0) {

                    $error = 'Wybrana kategoria nie istnieje.';

                } elseif (!is_dir($storageDir)) {

                    $error = 'Nie znaleziono folderu przechowywania plików PDF.';

                } elseif (!is_writable($storageDir)) {

                    $error = 'Brak uprawnień do zapisu plików PDF.';

                } else {

                    $destination = null;

                    try {
                        // 7. Bezpieczna nazwa pliku
                        $filename = bin2hex(
                            random_bytes(16)
                        ) . '.pdf';

                        $destination = $storageDir
                            . DIRECTORY_SEPARATOR
                            . $filename;

                        // 8. Przenosimy plik PDF
                        if (!move_uploaded_file(
                            $file['tmp_name'],
                            $destination
                        )) {
                            throw new RuntimeException(
                                'Nie udało się zapisać pliku PDF.'
                            );
                        }

                        // 9. Zapisujemy rekord w MySQL
                        $stmt = $pdo->prepare(
                            'INSERT INTO materials
                             (title, author, description,
                              category_id, file_path,
                              version, is_visible)
                             VALUES
                             (:title, :author, :description,
                              :category_id, :file_path,
                              1, 1)'
                        );

                        $stmt->execute([
                            'title' => $title,
                            'author' => $author,
                            'description' => $description,
                            'category_id' => $categoryId,
                            'file_path' => $filename
                        ]);

                        header(
                            'Location: add_material.php?success=1'
                        );
                        exit;

                    } catch (Throwable $e) {

                        // Usuwamy plik, jeśli rekord
                        // nie został zapisany w MySQL
                        if (
                            $destination !== null &&
                            is_file($destination)
                        ) {
                            unlink($destination);
                        }

                        error_log($e->getMessage());

                        $error = 'Nie udało się dodać materiału.';
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Dodawanie materiału</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<main class="container">

    <h1>Dodawanie materiału PDF</h1>

    <p>
        <a href="dashboard.php">
            ← Panel administratora
        </a>
    </p>

    <?php if (($_GET['success'] ?? '') === '1'): ?>
        <p class="success">
            Materiał został dodany pomyślnie!
        </p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p class="error">
            <?= esc($error) ?>
        </p>
    <?php endif; ?>

    <form
        id="materialForm"
        method="POST"
        action="add_material.php"
        enctype="multipart/form-data"
        class="material-form"
    >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= esc(csrfToken()) ?>"
        >

        <label for="title">Tytuł materiału:</label>
        <input
            type="text"
            id="title"
            name="title"
            maxlength="200"
            required
        >

        <label for="author">Autor lub organizacja:</label>
        <input
            type="text"
            id="author"
            name="author"
            maxlength="150"
            required
        >

        <label for="description">Opis:</label>
        <textarea
            id="description"
            name="description"
            maxlength="5000"
            rows="5"
            required
        ></textarea>

        <label for="category_id">Kategoria:</label>
        <select
            id="category_id"
            name="category_id"
            required
        >
            <option value="">Wybierz kategorię</option>

            <?php foreach ($categories as $category): ?>
                <option
                    value="<?= (int) $category['id'] ?>"
                >
                    <?= esc((string) $category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="pdf">Plik PDF:</label>
        <input
            type="file"
            id="pdf"
            name="pdf"
            accept=".pdf,application/pdf"
            required
        >

        <p>Maksymalny rozmiar pliku PDF: 20 MB.</p>

        <button type="submit">
            Dodaj materiał
        </button>

    </form>

</main>

<!-- Biblioteka JustValidate -->
<script src="https://unpkg.com/just-validate@4.3.0/dist/just-validate.production.min.js"></script>

<!-- Zasady walidacji formularza -->
<script src="../js/material-validation.js"></script>

</body>
</html>
