<?php
require 'config.php';  // Подключение файла конфигурации

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$sql = "SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(total_price) AS total_sales, COUNT(*) AS order_count
        FROM orders
        WHERE status != 'отказ'
        GROUP BY month
        ORDER BY month ASC";

$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo json_encode([]);
}
$conn->close();

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data, JSON_UNESCAPED_UNICODE);

?>
