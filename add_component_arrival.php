<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "computer_sales";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$component_id = isset($_POST['component_id']) ? intval($_POST['component_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;

if ($component_id <= 0 || $quantity <= 0 || $price <= 0) {
    die("Invalid input data.");
}

$sql = "INSERT INTO component_arrivals (component_id, quantity, price) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iid", $component_id, $quantity, $price);
if ($stmt->execute() === TRUE) {
    echo "New component arrival added successfully";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();

// Получение текущих количества и средней цены компонента
$current_sql = "
    SELECT quantity, price
    FROM components
    WHERE id = ?
";
$current_stmt = $conn->prepare($current_sql);
$current_stmt->bind_param("i", $component_id);
$current_stmt->execute();
$current_stmt->bind_result($current_quantity, $current_price);
$current_stmt->fetch();
$current_stmt->close();

// Расчет новой средней цены и общего количества
$new_quantity = $current_quantity + $quantity;
$new_price = (($current_price * $current_quantity) + ($price * $quantity)) / $new_quantity;

// Обновление средней цены и количества в таблице components
$update_sql = "
    UPDATE components
    SET quantity = ?, price = ?
    WHERE id = ?
";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("idi", $new_quantity, $new_price, $component_id);
if ($update_stmt->execute() === FALSE) {
    echo "Error updating component: " . $update_stmt->error;
}
$update_stmt->close();

$conn->close();
?>
