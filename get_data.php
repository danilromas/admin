<?php
require 'config.php';  // Подключение файла конфигурации


$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

$conn->set_charset("utf8mb4");


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    echo "0 results";
}
$conn->close();

echo json_encode($data);
?>
