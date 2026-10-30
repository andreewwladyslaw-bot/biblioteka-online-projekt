<?php
// Dzień 11: edycja opisu, wymiana pliku PDF oraz ukrywanie materiału.
require_once __DIR__ . '/../config/auth.php';
requireLogin('../login.php');
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]);
if (!is_int($id)) {
    http_response_code(404);
    exit('Nie znaleziono materiału.');
}

$error = '';
$storageDir = 'C:/xampp/biblioteka_private_pdfs'; // Jak w add_material.php / download.php
$maxSize = 20 * 1024 * 1024;

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')
    ->fetchAll(PDO::FETCH_ASSOC);

function loadMaterial(PDO $pdo, int $id): array|false
{
    $stmt = $pdo->prepare('SELECT id, title, author, description, category_id,
                                  file_path, version, is_visible
                           FROM materials WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$material = loadMaterial($pdo, $id);
if (!$material) {
    http_response_code(404);
    exit('Nie znaleziono materiału.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        exit('Nieprawidłowe żądanie.');
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'hide') {
        // Ukrywamy materiał, bez usuwania go i modyfikowania rejestru pobrań.
        $stmt = $pdo->prepare('UPDATE materials SET is_visible = 0 WHERE id = :id');
        $stmt->execute(['id' => $id]);
        header('Location: edit_material.php?id=' . $id . '&status=hidden');
        exit;
    }

    if ($action === 'save') {
        $title = is_string($_POST['title'] ?? null) ? trim($_POST['title']) : '';
        $author = is_string($_POST['author'] ?? null) ? trim($_POST['author']) : '';
        $description = is_string($_POST['description'] ?? null) ? trim($_POST['description']) : '';
        $categoryId = filter_var($_POST['category_id'] ?? null,
            FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $hasNewFile = isset($_FILES['pdf']) && is_array($_FILES['pdf'])
            && ($_FILES['pdf']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

        if ($title === '' || $author === '' || $description === '' || !is_int($categoryId)) {
            $error = 'Wypełnij wszystkie pola i wybierz kategorię.';
        } elseif (mb_strlen($title) > 200 || mb_strlen($author) > 150 ||
                  mb_strlen($description) > 5000) {
            $error = 'Zbyt długa wartość pola.';
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = :id');
            $stmt->execute(['id' => $categoryId]);
            if ((int) $stmt->fetchColumn() !== 1) {
                $error = 'Kategoria nie istnieje.';
            }
        }

        $newFilePath = null;
        $newFilename = null;
        if ($error === '' && $hasNewFile) {
            $f = $_FILES['pdf'];
            if (!is_int($f['error'] ?? null) || $f['error'] !== UPLOAD_ERR_OK) {
                $error = 'Błąd przesyłania pliku PDF. Sprawdź limity konfiguracji PHP.';
            } elseif (!is_int($f['size'] ?? null) || $f['size'] <= 0 ||
                      $f['size'] > $maxSize ||
                      !is_string($f['tmp_name'] ?? null) ||
                      !is_string($f['name'] ?? null) ||
                      !is_uploaded_file($f['tmp_name'])) {
                $error = 'Nieprawidłowy plik PDF lub rozmiar przekracza 20 MB.';
            } elseif (strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)) !== 'pdf') {
                $error = 'Dozwolone są wyłącznie pliki PDF.';
            } else {
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
                $stream = fopen($f['tmp_name'], 'rb');
                $signature = $stream ? fread($stream, 5) : '';
                if ($stream) { fclose($stream); }
                if ($mime !== 'application/pdf' || $signature !== '%PDF-') {
                    $error = 'Zawartość pliku nie jest dokumentem PDF.';
                } elseif (!is_dir($storageDir) || !is_writable($storageDir)) {
                    $error = 'Nie znaleziono folderu PDF lub brak uprawnień do zapisu.';
                } else {
                    $newFilename = bin2hex(random_bytes(16)) . '.pdf';
                    $newFilePath = $storageDir . DIRECTORY_SEPARATOR . $newFilename;
                    if (!move_uploaded_file($f['tmp_name'], $newFilePath)) {
                        $error = 'Nie udało się zapisać nowego pliku PDF.';
                        $newFilePath = null;
                    }
                }
            }
        } elseif ($error === '' && isset($_FILES['pdf']) && is_array($_FILES['pdf']) &&
                  ($_FILES['pdf']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            // Plik jest opcjonalny: można zmienić sam opis lub autora.
        }

        if ($error === '') {
            try {
                // Poprzedni rekord i plik PDF pozostają bez zmian do pomyślnego zapisu.
                $pdo->beginTransaction();
                $sql = 'UPDATE materials
                        SET title = :title, author = :author,
                            description = :description, category_id = :category_id';
                $args = [
                    'title' => $title,
                    'author' => $author,
                    'description' => $description,
                    'category_id' => $categoryId,
                    'id' => $id
                ];
                if ($newFilename !== null) {
                    $sql .= ', file_path = :file_path, version = version + 1';
                    $args['file_path'] = $newFilename;
                }
                $sql .= ' WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($args);
                $pdo->commit();

                // Nie usuwamy automatycznie poprzedniego pliku PDF.
                // Poprzednie wpisy w downloads również pozostają bez zmian.
                header('Location: edit_material.php?id=' . $id . '&status=updated');
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                if ($newFilePath !== null && is_file($newFilePath)) { unlink($newFilePath); }
                error_log($e->getMessage());
                $error = 'Nie udało się zapisać zmian.';
            }
        }
    } elseif ($action !== 'hide') {
        $error = 'Nieznana operacja.';
    }

    // Po błędzie zapisu przywracamy wartości z ostatniej próby.
    if ($action === 'save' && $error !== '') {
        $material['title'] = $title ?? $material['title'];
        $material['author'] = $author ?? $material['author'];
        $material['description'] = $description ?? $material['description'];
        if (isset($categoryId) && is_int($categoryId)) {
            $material['category_id'] = $categoryId;
        }
    }
}

$status = is_string($_GET['status'] ?? null) ? $_GET['status'] : '';
$notice = [
    'updated' => 'Materiał został zaktualizowany. Numer wersji zwiększono tylko po wymianie pliku PDF.',
    'hidden' => 'Materiał został ukryty. Historia pobrań została zachowana.'
][$status] ?? '';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edycja materiału</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<main class="container">
    <h1>Edycja materiału PDF</h1>
    <p><a href="materials.php">← Do listy materiałów</a></p>
    <?php if ($notice): ?><p class="success"><?= esc($notice) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= esc($error) ?></p><?php endif; ?>
    <p><strong>Aktualna wersja:</strong> <?= (int) $material['version'] ?></p>
    <p><strong>Status:</strong> <?= (int) $material['is_visible'] === 1 ? 'Widoczny' : 'Ukryty' ?></p>

    <form method="POST" action="edit_material.php?id=<?= $id ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= esc(csrfToken()) ?>">
        <input type="hidden" name="action" value="save">
        <p><label>Tytuł<br><input type="text" name="title" maxlength="200" required
                   value="<?= esc((string) $material['title']) ?>"></label></p>
        <p><label>Autor lub organizacja<br><input type="text" name="author" maxlength="150" required
                   value="<?= esc((string) $material['author']) ?>"></label></p>
        <p><label>Opis<br><textarea name="description" rows="5" maxlength="5000" required><?= esc((string) $material['description']) ?></textarea></label></p>
        <p><label>Kategoria<br><select name="category_id" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int) $cat['id'] ?>"
                    <?= (int) $material['category_id'] === (int) $cat['id'] ? 'selected' : '' ?>>
                    <?= esc((string) $cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select></label></p>
        <p><label>Nowy plik PDF (opcjonalnie; do 20 MB)<br>
            <input type="file" name="pdf" accept=".pdf,application/pdf"></label></p>
        <p>Jeśli nie wybierzesz pliku, zmienią się tylko dane materiału. Po wymianie pliku PDF numer wersji wzrośnie o 1.</p>
        <button type="submit">Zapisz zmiany</button>
    </form>

    <?php if ((int) $material['is_visible'] === 1): ?>
        <form method="POST" action="edit_material.php?id=<?= $id ?>"
              onsubmit="return confirm('Ukryć materiał? Historia pobrań zostanie zachowana.');">
            <input type="hidden" name="csrf_token" value="<?= esc(csrfToken()) ?>">
            <input type="hidden" name="action" value="hide">
            <button type="submit">Ukryj materiał</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
