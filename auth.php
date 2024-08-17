<!-- 
require 'config.php';

// Пример создания пользователей
$users = [
    ['username' => 'admin', 'password' => password_hash('Bracchium7', PASSWORD_DEFAULT), 'role' => 'admin'],
    ['username' => 'manager', 'password' => password_hash('Print0City', PASSWORD_DEFAULT), 'role' => 'manager'],
    ['username' => 'assembler', 'password' => password_hash('Horse5Good', PASSWORD_DEFAULT), 'role' => 'assembler'],
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
?> -->

<?php
session_start(); // Убедитесь, что session_start() вызывается в начале файла

// Функция проверки логина
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Функция проверки роли
function check_role($allowed_roles) {
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }

    // Проверка наличия роли пользователя в списке разрешённых ролей
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        return false; // Роль не соответствует
    }
    return true; // Роль соответствует
}
?>