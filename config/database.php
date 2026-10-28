<?php
/**
 * DB credentials MUST NOT be committed to Git.
 * Copy config/database.local.example.php to config/database.local.php
 * and provide the local MySQL password there.
 */
$localSettings = __DIR__ . '/database.local.php';
if (is_file($localSettings)) {
    $settings = require $localSettings;
} else {
    $settings = [
        'host' => getenv('BIBLIOTEKA_DB_HOST') ?: 'localhost',
        'name' => getenv('BIBLIOTEKA_DB_NAME') ?: 'biblioteka_online_test',
        'user' => getenv('BIBLIOTEKA_DB_USER') ?: 'biblioteka_app',
        'password' => getenv('BIBLIOTEKA_DB_PASSWORD') ?: '',
    ];
}

if (!is_array($settings) ||
    !isset($settings['host'], $settings['name'], $settings['user'], $settings['password']) ||
    !is_string($settings['password']) ||
    $settings['password'] === '' ||
    $settings['password'] === 'WPISZ_TUTAJ_HASLO_MYSQL') {
    http_response_code(500);
    exit('Не настроен MySQL: создайте config/database.local.php по образцу.');
}

try {
    $pdo = new PDO(
        'mysql:host=' . $settings['host'] . ';dbname=' . $settings['name'] . ';charset=utf8mb4',
        $settings['user'],
        $settings['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Ошибка подключения к базе данных.');
}
