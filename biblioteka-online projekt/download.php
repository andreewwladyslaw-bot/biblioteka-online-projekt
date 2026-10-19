
<?php
// День 9 — защищённое скачивание PDF

require_once __DIR__ . '/config/auth.php';
requireLogin();

require_once __DIR__ . '/config/database.php';

// 1. Получаем ID материала из URL
$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!is_int($id)) {
    http_response_code(404);
    exit('Материал не найден.');
}

// 2. Ищем только доступный материал
$stmt = $pdo->prepare(
    'SELECT id, title, file_path, is_visible
     FROM materials
     WHERE id = :id
       AND is_visible = 1
     LIMIT 1'
);

$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$material = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$material) {
    http_response_code(404);
    exit('Материал не найден или скрыт.');
}

// 3. Папка хранения PDF
$storageDir = 'C:/xampp/biblioteka_private_pdfs';

// 4. Получаем имя файла из базы
$filename = (string) $material['file_path'];

// Разрешаем только безопасные имена PDF
if (!preg_match(
    '/\A[A-Za-z0-9_-]+\.pdf\z/D',
    $filename
)) {
    http_response_code(404);
    exit('Файл недоступен.');
}

// 5. Проверяем настоящие пути
$storagePath = realpath($storageDir);

if ($storagePath === false) {
    http_response_code(500);
    exit('Хранилище PDF недоступно.');
}

$filepath = realpath(
    $storagePath . DIRECTORY_SEPARATOR . $filename
);

// Защита от выхода за пределы папки
if (
    $filepath === false ||
    !str_starts_with(
        $filepath,
        $storagePath . DIRECTORY_SEPARATOR
    ) ||
    !is_file($filepath) ||
    !is_readable($filepath)
) {
    http_response_code(404);
    exit('PDF-файл не найден.');
}

// 6. Открываем файл
$handle = fopen($filepath, 'rb');

if ($handle === false) {
    http_response_code(500);
    exit('Не удалось открыть PDF.');
}

$info = fstat($handle);

if ($info === false) {
    fclose($handle);
    http_response_code(500);
    exit('Не удалось проверить PDF.');
}

// 7. Отправляем заголовки для скачивания
header('Content-Type: application/pdf');

header(
    'Content-Disposition: attachment; filename="material-'
    . $id . '.pdf"'
);

header('Content-Length: ' . $info['size']);

header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');

// 8. Отправляем PDF браузеру
fpassthru($handle);

fclose($handle);
exit;
