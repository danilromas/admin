<?php
require 'config.php';

// Пример создания пользователей
$users = [
    ['username' => 'admin', 'password' => password_hash('admin_password', PASSWORD_DEFAULT), 'role' => 'admin'],
    ['username' => 'manager', 'password' => password_hash('manager_password', PASSWORD_DEFAULT), 'role' => 'manager'],
    ['username' => 'assembler', 'password' => password_hash('assembler_password', PASSWORD_DEFAULT), 'role' => 'assembler'],
];

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

foreach ($users as $user) {
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $user['username'], $user['password'], $user['role']);
    $stmt->execute();
}


$conn->close();
?>

<?php
// auth.php
session_start();

if (!function_exists('check_login')) {
    function check_login() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }
    }
}

if (!function_exists('check_role')) {
    function check_role($required_role) {
        if ($_SESSION['role'] !== $required_role) {
            header("Location: index.php");
            exit();
        }
    }
}


// auth.php
session_start();

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function check_role($allowed_roles) {
    // Преобразуем $allowed_roles в массив, если передано одно значение
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }

    // Проверяем, находится ли текущая роль пользователя в списке разрешённых ролей
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: index.php");
        exit();
    }
}
?>