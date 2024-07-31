<?php
require 'config.php';  // Подключение файла конфигурации


// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);

// Установка кодировки
if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
    exit();
}

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$component_id = $_GET['id'];

$sql = "SELECT price FROM components WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $component_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['price' => $row['price']]);
} else {
    echo json_encode(['price' => 0]);
}

$stmt->close();
$conn->close();
?>