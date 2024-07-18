<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "computer_sales";

// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);

// Установка кодировки
if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
    exit();
}

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получение данных из формы
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$price = isset($_POST['price']) ? trim($_POST['price']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';

// Проверка на наличие данных
if ($name == '' || $price == '' || $category == '') {
    die("Component name, price and category are required.");
}

// Проверка на корректность данных
if (!is_numeric($price) || $price <= 0) {
    die("Price must be a positive number.");
}

// SQL-запрос на вставку данных
$sql = "INSERT INTO components (name, price, category) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("sds", $name, $price, $category);

if ($stmt->execute() === TRUE) {
    echo "New component added successfully";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>