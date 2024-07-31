<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "computer_sales";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$category = isset($_POST['category']) ? $_POST['category'] : '';
$sql = "SELECT id, name FROM components WHERE category = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

$components = [];
while ($row = $result->fetch_assoc()) {
    $components[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($components);
?>
