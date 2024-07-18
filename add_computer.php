<?php
// Включить отображение всех ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключение к базе данных
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
$name = $_POST['name'];
$motherboard_id = $_POST['motherboard'];
$processor_id = $_POST['processor'];
$ram_id = $_POST['ram'];
$gpu_id = $_POST['gpu'];
$psu_id = $_POST['psu'];
$ssd_id = $_POST['ssd'];
$hdd_id = $_POST['hdd'];
$case_id = $_POST['case'];
$cpu_cooler_id = $_POST['cpu_cooler'];
$extra_cooler_id = $_POST['extra_cooler'];
$base_price = floatval($_POST['base_price']);
$markup = floatval($_POST['markup']);
$final_price = $base_price + $markup;

// Обработка загрузки изображения
$target_dir = "uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$target_file = $target_dir . basename($_FILES["case_photo"]["name"]);
if (!move_uploaded_file($_FILES["case_photo"]["tmp_name"], $target_file)) {
    die("Sorry, there was an error uploading your file.");
}

// SQL запрос для вставки данных в таблицу computers
$sql = "INSERT INTO computers (name, motherboard_id, processor_id, ram_id, gpu_id, psu_id, ssd_id, hdd_id, case_id, cpu_cooler_id, extra_cooler_id, case_photo, base_price, markup, final_price)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("siiiiiiiiissddd", $name, $motherboard_id, $processor_id, $ram_id, $gpu_id, $psu_id, $ssd_id, $hdd_id, $case_id, $cpu_cooler_id, $extra_cooler_id, $target_file, $base_price, $markup, $final_price);

if ($stmt->execute()) {
    echo "New computer build created successfully";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>