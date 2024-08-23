<?php
require 'config.php';  // Подключение файла конфигурации

// Установка заголовков для JSON и кодировки
header('Content-Type: application/json; charset=utf-8');

// Создание соединения с базой данных
$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

// Проверка соединения
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Установка кодировки соединения
$conn->set_charset("utf8mb4");

// Получение дат из GET-параметров с валидацией
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '1970-01-01';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Подготовка SQL-запроса
$sql = "SELECT date, city, total_price
        FROM orders
        WHERE status = 'куплен' AND date BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die(json_encode(['error' => 'Error preparing statement: ' . $conn->error]));
}

// Привязка параметров и выполнение запроса
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    $data = ['message' => 'Нет данных за выбранный период'];
}

// Закрытие запроса и соединения
$stmt->close();
$conn->close();

// Отправка данных в формате JSON
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>