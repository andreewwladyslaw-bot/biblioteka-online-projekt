<?php
require_once __DIR__ . '/config/auth.php';

requireLogin();

// Wylogowanie wyłącznie przez zabezpieczony formularz POST
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !is_string($_POST['csrf_token'] ?? null) ||
    !hash_equals(
        csrfToken(),
        $_POST['csrf_token']
    )
) {
    http_response_code(403);
    exit('Niedozwolone żądanie.');
}

// Czyścimy dane sesji
$_SESSION = [];

// Usuwamy plik cookie sesji
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $params['path'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax'
    ]);
}

// Kończymy sesję
session_destroy();

// Przekierowujemy na stronę logowania
header('Location: login.php');
exit;
