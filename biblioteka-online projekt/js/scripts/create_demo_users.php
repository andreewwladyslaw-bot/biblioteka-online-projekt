
<?php

// Запускать только из терминала
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Доступ запрещён');
}

function ask($message) {
    echo $message;
    return trim(fgets(STDIN));
}

$dbUser = ask("Пользователь MySQL: ");
$dbPass = ask("Пароль MySQL: ");

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=biblioteka_online_test;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $accounts = [
        ['Администратор', 'admin_demo', 'admin'],
        ['Сотрудник 1', 'employee1', 'employee'],
        ['Сотрудник 2', 'employee2', 'employee']
    ];

    $stmt = $pdo->prepare(
        "INSERT INTO users
         (full_name, username, password_hash, role)
         VALUES (?, ?, ?, ?)"
    );

    foreach ($accounts as [$name, $login, $role]) {
        $password = ask("Тестовый пароль для $login: ");

        if (strlen($password) < 12) {
            echo "Минимум 12 символов. Аккаунт пропущен.\n";
            continue;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt->execute([$name, $login, $hash, $role]);

        echo "Создан пользователь: $login\n";
    }

} catch (PDOException $e) {
    echo "Ошибка базы данных: " . $e->getMessage() . "\n";
}
?>
