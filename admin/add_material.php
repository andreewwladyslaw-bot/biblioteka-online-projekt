
<?php
require_once __DIR__ . '/../config/auth.php';

requireLogin('../login.php');
requireAdmin();

require_once __DIR__ . '/../config/database.php';

$error = '';
$maxSize = 20 * 1024 * 1024; // 20 МБ

// Общая защищённая папка для загрузки, редактирования и скачивания
require_once __DIR__ . '/../config/storage.php';
$storageDir = pdfStorageDir();

// Получаем категории из MySQL
$stmt = $pdo->query(
    'SELECT id, name
     FROM categories
     ORDER BY name'
);

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Проверка CSRF
    $token = $_POST['csrf_token'] ?? null;

    if (
        !is_string($token) ||
        !hash_equals(csrfToken(), $token)
    ) {
        http_response_code(403);
        exit('Недействительный запрос.');
    }

    // 2. Получение данных
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

    // 3. Проверка обязательных полей
    if (
        $title === '' ||
        $author === '' ||
        $description === '' ||
        !is_int($categoryId)
    ) {
        $error = 'Заполните все обязательные поля.';

    } elseif (
        mb_strlen($title) > 200 ||
        mb_strlen($author) > 150 ||
        mb_strlen($description) > 5000
    ) {
        $error = 'Превышена допустимая длина текста.';

    } elseif (
        !isset($_FILES['pdf']) ||
        !is_array($_FILES['pdf']) ||
        !isset($_FILES['pdf']['error']) ||
        !is_int($_FILES['pdf']['error'])
    ) {
        $error = 'Выберите PDF-файл.';

    } else {

        $file = $_FILES['pdf'];

        // 4. Проверка ошибок загрузки
        if ($file['error'] !== UPLOAD_ERR_OK) {

            $error = 'Ошибка загрузки файла. '
                . 'Проверьте размер и настройки PHP.';

        } elseif (
            !isset($file['size'], $file['tmp_name'],
                    $file['name']) ||
            !is_int($file['size']) ||
            !is_string($file['tmp_name']) ||
            !is_string($file['name']) ||
            $file['size'] <= 0 ||
            $file['size'] > $maxSize
        ) {
            $error = 'PDF должен быть не больше 20 МБ.';

        } elseif (
            strtolower(pathinfo(
                $file['name'],
                PATHINFO_EXTENSION
            )) !== 'pdf'
        ) {
            $error = 'Разрешён только формат PDF.';

        } elseif (!is_uploaded_file($file['tmp_name'])) {

            $error = 'Недопустимый загруженный файл.';

        } else {

            // 5. Проверка MIME-типа и заголовка PDF
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
                $error = 'Файл не является допустимым PDF.';
            } else {

                // 6. Проверка существования категории
                $stmt = $pdo->prepare(
                    'SELECT COUNT(*)
                     FROM categories
                     WHERE id = :id'
                );

                $stmt->execute([
                    'id' => $categoryId
                ]);

                if ((int) $stmt->fetchColumn() === 0) {

                    $error = 'Выбранная категория не существует.';

                } elseif (!is_dir($storageDir)) {

                    $error = 'Папка хранения PDF не найдена.';

                } elseif (!is_writable($storageDir)) {

                    $error = 'Нет прав на запись PDF.';

                } else {

                    $destination = null;

                    try {
                        // 7. Безопасное имя файла
                        $filename = bin2hex(
                            random_bytes(16)
                        ) . '.pdf';

                        $destination = $storageDir
                            . DIRECTORY_SEPARATOR
                            . $filename;

                        // 8. Перемещаем PDF
                        if (!move_uploaded_file(
                            $file['tmp_name'],
                            $destination
                        )) {
                            throw new RuntimeException(
                                'Не удалось сохранить PDF.'
                            );
                        }

                        // 9. Сохраняем запись в MySQL
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

                        // Удаляем файл, если запись
                        // в MySQL не сохранилась
                        if (
                            $destination !== null &&
                            is_file($destination)
                        ) {
                            unlink($destination);
                        }

                        error_log($e->getMessage());

                        $error = 'Не удалось добавить материал.';
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Добавление материала</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<main class="container">

    <h1>Добавление PDF-материала</h1>

    <p>
        <a href="dashboard.php">
            ← Панель администратора
        </a>
    </p>

    <?php if (($_GET['success'] ?? '') === '1'): ?>
        <p class="success">
            Материал успешно добавлен!
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

        <label for="title">Название материала:</label>
        <input
            type="text"
            id="title"
            name="title"
            maxlength="200"
            required
        >

        <label for="author">Автор или организация:</label>
        <input
            type="text"
            id="author"
            name="author"
            maxlength="150"
            required
        >

        <label for="description">Описание:</label>
        <textarea
            id="description"
            name="description"
            maxlength="5000"
            rows="5"
            required
        ></textarea>

        <label for="category_id">Категория:</label>
        <select
            id="category_id"
            name="category_id"
            required
        >
            <option value="">Выберите категорию</option>

            <?php foreach ($categories as $category): ?>
                <option
                    value="<?= (int) $category['id'] ?>"
                >
                    <?= esc((string) $category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="pdf">PDF-файл:</label>
        <input
            type="file"
            id="pdf"
            name="pdf"
            accept=".pdf,application/pdf"
            required
        >

        <p>Максимальный размер PDF: 20 МБ.</p>

        <button type="submit">
            Добавить материал
        </button>

    </form>

</main>

<!-- Библиотека JustValidate -->
<script src="https://unpkg.com/just-validate@4.3.0/dist/just-validate.production.min.js"></script>

<!-- Правила проверки формы -->
<script src="../js/material-validation.js"></script>

</body>
</html>
