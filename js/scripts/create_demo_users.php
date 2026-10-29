<?php

// Uruchamiać wyłącznie w terminalu
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Odmowa dostępu');
}

function ask($message) {
    echo $message;
    return trim(fgets(STDIN));
}

$dbUser = ask("Użytkownik MySQL: ");
$dbPass = ask("Hasło MySQL: ");

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=biblioteka_online_test;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $accounts = [
        ['Administrator', 'admin_demo', 'admin'],
        ['Pracownik 1', 'employee1', 'employee'],
        ['Pracownik 2', 'employee2', 'employee']
    ];

    $stmt = $pdo->prepare(
        "INSERT INTO users
         (full_name, username, password_hash, role)
         VALUES (?, ?, ?, ?)"
    );

    foreach ($accounts as [$name, $login, $role]) {
        $password = ask("Hasło testowe dla $login: ");

        if (strlen($password) < 12) {
            echo "Minimum 12 znaków. Konto zostało pominięte.\n";
            continue;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt->execute([$name, $login, $hash, $role]);

        echo "Utworzono użytkownika: $login\n";
    }

} catch (PDOException $e) {
    echo "Błąd bazy danych: " . $e->getMessage() . "\n";
}
?>
