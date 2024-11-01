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