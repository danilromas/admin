<?php
// Подключаем настройки из config.php
include_once 'config.php';

// Подключение к базе данных
$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

// Проверка соединения
if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

// Функция для проверки роли пользователя
function check_role($required_roles = []) {
    session_start();  // Начинаем сессию, если это необходимо

    // Для примера, возьмем роль из сессии (предположим, что роль хранится в $_SESSION['role'])
    $user_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

    // Проверяем, есть ли у пользователя требуемая роль
    return in_array($user_role, $required_roles);
}

// Проверка роли пользователя
$is_admin = check_role(['admin']); // Проверка, является ли пользователь администратором

if (!$is_admin) {
    die("Доступ запрещен. Вы не администратор.");
}

// Запрос для выборки данных о изменениях в таблице history
$sql = "SELECT id, action_type, table_name, record_id, old_value, new_value, change_date
        FROM history
        ORDER BY change_date DESC";

$result = $conn->query($sql);

// Проверка на ошибки запроса
if ($result === false) {
    die("Ошибка выполнения запроса: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История изменений в базе данных</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

<h2>История изменений в базе данных</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Тип действия</th>
        <th>Таблица</th>
        <th>Запись ID</th>
        <th>Старое значение</th>
        <th>Новое значение</th>
        <th>Дата изменения</th>
    </tr>

    <?php if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['id']) . "</td>
                    <td>" . htmlspecialchars($row['action_type']) . "</td>
                    <td>" . htmlspecialchars($row['table_name']) . "</td>
                    <td>" . htmlspecialchars($row['record_id']) . "</td>
                    <td>" . htmlspecialchars($row['old_value'] ?? '-') . "</td>
                    <td>" . htmlspecialchars($row['new_value'] ?? '-') . "</td>
                    <td>" . htmlspecialchars($row['change_date']) . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='7'>История изменений пуста.</td></tr>";
    }
    ?>

</table>

</body>
</html>
