<?php
// День 11: изменение описания, замена PDF и скрытие материала.
require_once __DIR__ . '/../config/auth.php';
requireLogin('../login.php');
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]);
if (!is_int($id)) {
    http_response_code(404);
    exit('Материал не найден.');
}

$error = '';
$storageDir = 'C:/xampp/biblioteka_private_pdfs'; // Как в add_material.php / download.php
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
    exit('Материал не найден.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        exit('Недействительный запрос.');
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'hide') {
        // Скрываем запись, но не удаляем её и не изменяем журнал скачиваний.
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
            $error = 'Заполните все поля и выберите категорию.';
        } elseif (mb_strlen($title) > 200 || mb_strlen($author) > 150 ||
                  mb_strlen($description) > 5000) {
            $error = 'Слишком длинное значение поля.';
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = :id');
            $stmt->execute(['id' => $categoryId]);
            if ((int) $stmt->fetchColumn() !== 1) {
                $error = 'Категория не существует.';
            }
        }

        $newFilePath = null;
        $newFilename = null;
        if ($error === '' && $hasNewFile) {
            $f = $_FILES['pdf'];
            if (!is_int($f['error'] ?? null) || $f['error'] !== UPLOAD_ERR_OK) {
                $error = 'Ошибка загрузки PDF. Проверьте ограничения PHP.';
            } elseif (!is_int($f['size'] ?? null) || $f['size'] <= 0 ||
                      $f['size'] > $maxSize ||
                      !is_string($f['tmp_name'] ?? null) ||
                      !is_string($f['name'] ?? null) ||
                      !is_uploaded_file($f['tmp_name'])) {
                $error = 'Неверный PDF или размер больше 20 МБ.';
            } elseif (strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)) !== 'pdf') {
                $error = 'Разрешены только PDF-файлы.';
            } else {
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
                $stream = fopen($f['tmp_name'], 'rb');
                $signature = $stream ? fread($stream, 5) : '';
                if ($stream) { fclose($stream); }
                if ($mime !== 'application/pdf' || $signature !== '%PDF-') {
                    $error = 'Содержимое файла не является PDF.';
                } elseif (!is_dir($storageDir) || !is_writable($storageDir)) {
                    $error = 'Папка PDF не найдена или недоступна для записи.';
                } else {
                    $newFilename = bin2hex(random_bytes(16)) . '.pdf';
                    $newFilePath = $storageDir . DIRECTORY_SEPARATOR . $newFilename;
                    if (!move_uploaded_file($f['tmp_name'], $newFilePath)) {
                        $error = 'Не удалось сохранить новый PDF.';
                        $newFilePath = null;
                    }
                }
            }
        } elseif ($error === '' && isset($_FILES['pdf']) && is_array($_FILES['pdf']) &&
                  ($_FILES['pdf']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            // Файл необязателен: разрешено изменить только описание/автора.
        }

        if ($error === '') {
            try {
                // Старую запись и старый PDF не заменяем до успешного сохранения.
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

                // Старый PDF сознательно не удаляем автоматически.
                // Старые записи downloads тоже не изменяются.
                header('Location: edit_material.php?id=' . $id . '&status=updated');
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                if ($newFilePath !== null && is_file($newFilePath)) { unlink($newFilePath); }
                error_log($e->getMessage());
                $error = 'Не удалось сохранить изменения.';
            }
        }
    } elseif ($action !== 'hide') {
        $error = 'Неизвестное действие.';
    }

    // Возвращаем поля последней попытки при ошибке сохранения.
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
    'updated' => 'Материал успешно изменён. Версия увеличена только при замене PDF.',
    'hidden' => 'Материал скрыт. История скачиваний сохранена.'
][$status] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование материала</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<main class="container">
    <h1>Редактирование PDF-материала</h1>
    <p><a href="materials.php">← К списку материалов</a></p>
    <?php if ($notice): ?><p class="success"><?= esc($notice) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= esc($error) ?></p><?php endif; ?>
    <p><strong>Текущая версия:</strong> <?= (int) $material['version'] ?></p>
    <p><strong>Статус:</strong> <?= (int) $material['is_visible'] === 1 ? 'Виден' : 'Скрыт' ?></p>

    <form method="POST" action="edit_material.php?id=<?= $id ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= esc(csrfToken()) ?>">
        <input type="hidden" name="action" value="save">
        <p><label>Название<br><input type="text" name="title" maxlength="200" required
                   value="<?= esc((string) $material['title']) ?>"></label></p>
        <p><label>Автор или организация<br><input type="text" name="author" maxlength="150" required
                   value="<?= esc((string) $material['author']) ?>"></label></p>
        <p><label>Описание<br><textarea name="description" rows="5" maxlength="5000" required><?= esc((string) $material['description']) ?></textarea></label></p>
        <p><label>Категория<br><select name="category_id" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int) $cat['id'] ?>"
                    <?= (int) $material['category_id'] === (int) $cat['id'] ? 'selected' : '' ?>>
                    <?= esc((string) $cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select></label></p>
        <p><label>Новый PDF (необязательно; до 20 МБ)<br>
            <input type="file" name="pdf" accept=".pdf,application/pdf"></label></p>
        <p>Если не выбирать файл, изменится только описание. При замене PDF версия увеличится на 1.</p>
        <button type="submit">Сохранить изменения</button>
    </form>

    <?php if ((int) $material['is_visible'] === 1): ?>
        <form method="POST" action="edit_material.php?id=<?= $id ?>"
              onsubmit="return confirm('Скрыть материал? История останется.');">
            <input type="hidden" name="csrf_token" value="<?= esc(csrfToken()) ?>">
            <input type="hidden" name="action" value="hide">
            <button type="submit">Скрыть материал</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
