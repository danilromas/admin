<?php
require 'config.php';  // Подключение файла конфигурации

// Подключение к базе данных с установкой кодировки
$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $computer_id = $_POST['computer_id'];
    $date = $_POST['date'];
    $name = $_POST['name'];
    $city = $_POST['city'];
    $delivery = $_POST['delivery'];
    $additional = $_POST['additional'];
    $additional_price = $_POST['additional_price'];
    $total_price = $_POST['total_price'];

    $sql = "INSERT INTO orders (computer_id, date, name, city, delivery, additional, additional_price, total_price) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("isssssdd", $computer_id, $date, $name, $city, $delivery, $additional, $additional_price, $total_price);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    echo "Order created successfully!";
    $stmt->close();
}

$conn->close();
?>
