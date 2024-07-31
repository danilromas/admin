<?php
require 'config.php';  // Подключение файла конфигурации


$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category = $_POST['category'];
    $sql = "SELECT id, name FROM components WHERE category = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();

    $components = [];
    while ($row = $result->fetch_assoc()) {
        $components[] = $row;
    }
    echo json_encode($components);
}

$conn->close();
?>
