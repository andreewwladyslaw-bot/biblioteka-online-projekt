
<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';

// Если пользователь уже вошёл
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Проверка защитного токена
    if (
        !is_string($_POST['csrf_token'] ?? null) ||
        !hash_equals(
            csrfToken(),
            $_POST['csrf_token']
        )
    ) {
        http_response_code(403);
        $error = 'Недействительная форма. Обновите страницу.';
    } else {

        $username = trim(
            (string) ($_POST['username'] ?? '')
        );

        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Заполните все поля.';
        } else {

            // Поиск пользователя в базе
            $stmt = $pdo->prepare(
                'SELECT id, full_name, username,
                        password_hash, role
                 FROM users
                 WHERE username = :username
                 LIMIT 1'
            );

            $stmt->execute([
                'username' => $username
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Проверка пароля и роли
            if (
                $user &&
                password_verify(
                    $password,
                    $user['password_hash']
                ) &&
                in_array(
                    $user['role'],
                    ['admin', 'employee'],
                    true
                )
            ) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                unset($_SESSION['csrf_token']);

                header('Location: index.php');
                exit;
            }

            $error = 'Неверный логин или пароль.';
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
    <title>Вход — Электронная библиотека</title>
</head>
<body>

    <h1>Электронная библиотека компании</h1>
    <h2>Вход в систему</h2>

    <?php if ($error !== ''): ?>
        <p style="color: red;">
            <?= esc($error) ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="login.php">

        <label for="username">Логин:</label>
        <input
            type="text"
            id="username"
            name="username"
            autocomplete="username"
            required
        >

        <label for="password">Пароль:</label>
        <input
            type="password"
            id="password"
            name="password"
            autocomplete="current-password"
            required
        >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= esc(csrfToken()) ?>"
        >

        <button type="submit">Войти</button>
    </form>

</body>
</html>
