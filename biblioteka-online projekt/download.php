
<?php
// День 10 — защищённое скачивание и журнал F07

require_once __DIR__ . '/config/auth.php';
requireLogin();

require_once __DIR__ . '/config/database.php';

// 1. Получаем ID материала
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

// 2. Получаем видимый материал из MySQL
$stmt = $pdo->prepare(
    'SELECT id, title, file_path, version, is_visible
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

// 3. Проверяем имя PDF
$filename = (string) $material['file_path'];

if (!preg_match(
    '/\A[A-Za-z0-9_-]+\.pdf\z/D',
    $filename
)) {
    http_response_code(404);
    exit('Файл недоступен.');
}

// 4. Определяем закрытую папку хранения
$storageDir = 'C:/xampp/biblioteka_private_pdfs';

$storagePath = realpath($storageDir);

if ($storagePath === false) {
    http_response_code(500);
    exit('Хранилище PDF недоступно.');
}

$filepath = realpath(
    $storagePath . DIRECTORY_SEPARATOR . $filename
);

// Проверяем путь и наличие файла
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

// 5. Открываем PDF
$handle = @fopen($filepath, 'rb');

if ($handle === false) {
    http_response_code(500);
    exit('Не удалось открыть PDF.');
}

$info = fstat($handle);

if (
    $info === false ||
    $info['size'] <= 0
) {
    fclose($handle);
    http_response_code(500);
    exit('PDF-файл пуст или недоступен.');
}

// Проверяем возможность чтения
$firstChunk = fread($handle, 8192);

if ($firstChunk === false || $firstChunk === '') {
    fclose($handle);
    http_response_code(500);
    exit('Не удалось прочитать PDF.');
}

// 6. Данные скачивания
$userId = (int) $_SESSION['user_id'];
$materialId = (int) $material['id'];
$fileVersion = (int) $material['version'];

$stmt = $pdo->prepare(
    'INSERT INTO downloads
     (user_id, material_id, file_version)
     VALUES (:user_id, :material_id, :file_version)'
);

$stmt->execute([
    'user_id' => $userId,
    'material_id' => $materialId,
    'file_version' => $fileVersion
]);

// 7. Записываем начало разрешённой выдачи
// Непосредственно перед отправкой PDF
try {
    $stmt = $pdo->prepare(
        'INSERT INTO downloads
         (user_id, material_id, file_version)
         VALUES
         (:user_id, :material_id, :file_version)'
    );

    $stmt->execute([
        'user_id' => $userId,
        'material_id' => $materialId,
        'file_version' => $fileVersion
    ]);

} catch (PDOException $e) {
    fclose($handle);
    error_log($e->getMessage());

    http_response_code(500);
    exit('Ошибка записи скачивания.');
}

// 8. Освобождаем блокировку сессии
session_write_close();

// 9. Отправляем PDF
header('Content-Type: application/pdf');

header(
    'Content-Disposition: attachment; filename="material-'
    . $materialId . '.pdf"'
);

header('Content-Length: ' . $info['size']);
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');

// Начало передачи файла
echo $firstChunk;

// Отправляем оставшуюся часть
fpassthru($handle);

fclose($handle);
exit;

