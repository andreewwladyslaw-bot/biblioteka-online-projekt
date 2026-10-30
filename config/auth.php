<?php
// Uruchomienie sesji użytkownika
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// Sprawdzenie, czy użytkownik jest zalogowany
function requireLogin(string $loginUrl = 'login.php'): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . $loginUrl);
        exit;
    }
}

// Weryfikacja roli administratora
function requireAdmin(): void
{
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Odmowa dostępu.');
    }
}

// Token zabezpieczający formularze
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION['csrf_token'];
}

// Bezpieczne wyświetlanie tekstu
function esc(string $text): string
{
    return htmlspecialchars(
        $text,
        ENT_QUOTES,
        'UTF-8'
    );
}
