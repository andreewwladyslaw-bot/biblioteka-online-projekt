
<?php
require_once __DIR__ . '/config/auth.php';

requireLogin();

// Выход только через защищённую POST-форму
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !is_string($_POST['csrf_token'] ?? null) ||
    !hash_equals(
        csrfToken(),
        $_POST['csrf_token']
    )
) {
    http_response_code(403);
    exit('Недопустимый запрос.');
}

// Удаляем данные сессии
$_SESSION = [];

// Удаляем cookie сессии
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

// Завершаем сессию
session_destroy();

// Возвращаем на страницу входа
header('Location: login.php');
exit;
