<?php
require 'config.php';  // Подключение файла конфигурации

// Устанавливаем заголовок, чтобы указать, что ответ будет в формате JSON с кодировкой UTF-8
header('Content-Type: application/json; charset=utf-8');

// Подключаемся к базе данных
$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

// Проверяем соединение
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Устанавливаем кодировку соединения
$conn->set_charset("utf8mb4");

// Выполняем SQL-запрос
$sql = "SELECT * FROM orders ORDER BY date DESC LIMIT 10";
$result = $conn->query($sql);

$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
} else {
    echo json_encode(["message" => "0 results"]);
    $conn->close();
    exit();
}

$conn->close();

// Кодируем данные в JSON-формат с флагом JSON_UNESCAPED_UNICODE, чтобы избежать экранирования юникод-символов
echo json_encode($orders, JSON_UNESCAPED_UNICODE);
?>
