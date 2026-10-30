<?php
// Skopiuj dane do swojego lokalnego config/database.php i wpisz własne hasło.
$host = 'localhost';
$dbname = 'biblioteka_online_test';
$username = 'biblioteka_app';
$password = 'TU_WPISZ_HASLO_LOKALNE';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Błąd połączenia z bazą danych.');
}
