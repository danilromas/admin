<?php
require 'auth.php';
check_login();
check_role(['admin']);
?>

<?php
require 'config.php';  // Подключение файла конфигурации

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if ($conn->connect_error) {
    die("Не удалось подключиться к базе данных: " . $conn->connect_error);
}

// Получение данных из формы
$category = $_POST['category'];
$component_id = $_POST['component_id'];
$quantity = $_POST['quantity'];
$price = $_POST['price'];
$invoice_number = $_POST['invoice_number'];
$delivery_date = date('Y-m-d', strtotime($_POST['delivery_date']));

// Подготовка SQL-запроса
$sql = "INSERT INTO component_arrivals (component_id, quantity, price, arrival_date, invoice_number, delivery_date, status) 
        VALUES (?, ?, ?, NOW(), ?, ?, 'in_transit')";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Ошибка подготовки запроса: " . $conn->error);
}

// Обратите внимание, что `delivery_date` и `invoice_number` должны быть строками ('s')
$stmt->bind_param("iisss", $component_id, $quantity, $price, $invoice_number, $delivery_date);

// Выполнение запроса
if ($stmt->execute()) {
    echo "Arrival added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
