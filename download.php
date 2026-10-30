<?php
// Dzień 10 — bezpieczne pobieranie plików i rejestr F07

require_once __DIR__ . '/config/auth.php';
requireLogin();

require_once __DIR__ . '/config/database.php';

// 1. Pobieramy identyfikator materiału
$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!is_int($id)) {
    http_response_code(404);
    exit('Nie znaleziono materiału.');
}

// 2. Pobieramy widoczny materiał z MySQL
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
    exit('Nie znaleziono materiału lub został on ukryty.');
}

// 3. Sprawdzamy nazwę pliku PDF
$filename = (string) $material['file_path'];

if (!preg_match(
    '/\A[A-Za-z0-9_-]+\.pdf\z/D',
    $filename
)) {
    http_response_code(404);
    exit('Plik jest niedostępny.');
}

// 4. Ustalamy prywatny katalog plików
$storageDir = 'C:/xampp/biblioteka_private_pdfs';

$storagePath = realpath($storageDir);

if ($storagePath === false) {
    http_response_code(500);
    exit('Magazyn plików PDF jest niedostępny.');
}

$filepath = realpath(
    $storagePath . DIRECTORY_SEPARATOR . $filename
);

// Sprawdzamy ścieżkę i obecność pliku
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
    exit('Nie znaleziono pliku PDF.');
}

// 5. Otwieramy plik PDF
$handle = @fopen($filepath, 'rb');

if ($handle === false) {
    http_response_code(500);
    exit('Nie udało się otworzyć pliku PDF.');
}

$info = fstat($handle);

if (
    $info === false ||
    $info['size'] <= 0
) {
    fclose($handle);
    http_response_code(500);
    exit('Plik PDF jest pusty lub niedostępny.');
}

// Sprawdzamy możliwość odczytu
$firstChunk = fread($handle, 8192);

if ($firstChunk === false || $firstChunk === '') {
    fclose($handle);
    http_response_code(500);
    exit('Nie udało się odczytać pliku PDF.');
}

// 6. Dane pobrania
$userId = (int) $_SESSION['user_id'];
$materialId = (int) $material['id'];
$fileVersion = (int) $material['version'];



// 7. Zapisujemy rozpoczęcie dozwolonego pobierania
// Bezpośrednio przed wysłaniem pliku PDF
try {
    $stmt = $pdo->prepare(
        'INSERT INTO downloads
         (user_id, material_id, file_version)
         VALUES
         (:user_id, :material_id, :file_version)'
    );

    $stmt->execute([
        'user_id' => (int) $_SESSION['user_id'],
        'material_id' => (int) $material['id'],
        'file_version' => (int) $material['version']
    ]);

} catch (PDOException $e) {
    fclose($handle);
    error_log($e->getMessage());

    http_response_code(500);
    exit('Nie udało się zapisać pobrania.');
}

// 8. Zwalniamy blokadę sesji
session_write_close();

// 9. Wysyłamy plik PDF
header('Content-Type: application/pdf');

header(
    'Content-Disposition: attachment; filename="material-'
    . $materialId . '.pdf"'
);

header('Content-Length: ' . $info['size']);
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');

// Początek przesyłania pliku
echo $firstChunk;

// Przesyłamy pozostałą część
fpassthru($handle);

fclose($handle);
exit;

