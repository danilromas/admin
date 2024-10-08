<?php
require 'auth.php';
check_login(); // Проверяет, авторизован ли пользователь

$is_admin = check_role(['admin']); // Проверяет, имеет ли пользователь роль администратора

if (!$is_admin) {
    header("Location: index.php"); // Перенаправление, если роль не соответствует
    exit();
}


require 'config.php';  // Подключение файла конфигурации


// Создание соединения
$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

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
$photo = isset($_FILES['photo']) ? $_FILES['photo'] : null;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

// Проверка на наличие данных
if ($name == '' || $price == '' || $category == '' || $quantity == 0) {
    die("Component name, price, category, photo, and quantity are required.");
}

// Проверка на корректность данных
if (!is_numeric($price) || $price <= 0) {
    die("Price must be a positive number.");
}

// Проверка на наличие фотографии и корректность загрузки
if ($photo && $photo['error'] == 0) {
    // Путь для сохранения загруженного файла
    $uploadDir = 'uploads/';
    $uploadFile = $uploadDir . basename($photo['name']);

    // Перемещение загруженного файла в директорию
    if (move_uploaded_file($photo['tmp_name'], $uploadFile)) {
        $photoPath = $uploadFile;
    } else {
        die("Error uploading photo.");
    }
} else {
    die("Photo is required.");
}

// SQL-запрос на вставку данных
$sql = "INSERT INTO components (name, price, category, photo, quantity) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("sdssi", $name, $price, $category, $photoPath, $quantity);

if ($stmt->execute() === TRUE) {
    echo "New component added successfully";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
