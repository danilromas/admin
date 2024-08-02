<?php
require 'config.php';

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT price FROM components WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($price);
    if ($stmt->fetch()) {
        echo json_encode(['price' => $price]);
    } else {
        echo json_encode(['price' => 0]);
    }
    $stmt->close();
}

$conn->close();
?>
