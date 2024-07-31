<?php
require 'config.php';  // Подключение файла конфигурации


$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$startDate = $_GET['start_date'];
$endDate = $_GET['end_date'];

$sql = "SELECT date, city, total_price
        FROM orders
        WHERE status != 'отказ' AND date BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo "0 results";
}
$stmt->close();
$conn->close();

echo json_encode($data);
?>
