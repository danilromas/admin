<?php
// Подключение к базе данных
include 'config.php';

// Получаем все записи из таблицы security_info
$sql = "
    SELECT 
        id AS order_id,       -- ID заказа (или любой уникальный идентификатор)
        phone AS client_phone, -- Телефон клиента
        name_avito AS client_avito_name, -- Имя на Авито
        link_avito AS client_avito_link  -- Ссылка на Авито
    FROM security_info
    WHERE phone IS NOT NULL  -- Убедитесь, что у вас есть данные для вывода
    ORDER BY id DESC
";

// Создание соединения
$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
// Проверка соединения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Информация о клиентах</title>
    <link rel="stylesheet" href="styles.css"> <!-- Подключите свой CSS файл -->
</head>
<body>
    <h1>Информация о клиентах</h1>
    <table>
        <thead>
            <tr>
                <th>ID Заказа</th>
                <th>Телефон клиента</th>
                <th>Имя на Авито</th>
                <th>Ссылка на Авито</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['order_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['client_phone'] ?? 'Не указано') . "</td>";
                    echo "<td>" . htmlspecialchars($row['client_avito_name'] ?? 'Не указано') . "</td>";
                    echo "<td><a href='" . htmlspecialchars($row['client_avito_link'] ?? '#') . "' target='_blank'>Ссылка</a></td>";
                    echo "<td><a href='orders.php'>Посмотреть заказ</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Нет доступных записей.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
