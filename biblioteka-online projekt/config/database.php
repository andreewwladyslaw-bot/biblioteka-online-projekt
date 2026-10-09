<?php

$host = 'localhost';
$dbname = 'biblioteka_online';
$username = 'biblioteka_app';
$password = 'NoweHaslo123!';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych.");
}
?>