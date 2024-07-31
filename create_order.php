<?php
require 'config.php';  // Подключение файла конфигурации


$conn = new mysqli($servername, $username, $password, $dbname);

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

    $sql = "INSERT INTO orders (computer_id, date, name, city, delivery, additional, additional_price, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'принят')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssd", $computer_id, $date, $name, $city, $delivery, $additional, $additional_price, $total_price);

    if ($stmt->execute()) {
        header("Location: orders.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
