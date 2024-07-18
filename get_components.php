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

// Получение категории компонентов из GET параметра
$category = $_GET['category'];

// Подготовка SQL запроса в зависимости от выбранной категории
$sql = "";
switch ($category) {
    case "Материнская плата":
        $sql = "SELECT id, name FROM components WHERE category = 'Материнская плата'";
        break;
    case "Процессор":
        $sql = "SELECT id, name FROM components WHERE category = 'Процессор'";
        break;
    case "Оперативная память":
        $sql = "SELECT id, name FROM components WHERE category = 'Оперативная память'";
        break;
    case "Видеокарта":
        $sql = "SELECT id, name FROM components WHERE category = 'Видеокарта'";
        break;
    case "Блок питания":
        $sql = "SELECT id, name FROM components WHERE category = 'Блок питания'";
        break;
    case "SSD диск":
        $sql = "SELECT id, name FROM components WHERE category = 'SSD диск'";
        break;
    case "HDD диск":
        $sql = "SELECT id, name FROM components WHERE category = 'HDD диск'";
        break;
    case "Корпус":
        $sql = "SELECT id, name FROM components WHERE category = 'Корпус'";
        break;
    case "Куллер (процессор)":
        $sql = "SELECT id, name FROM components WHERE category = 'Куллер (процессор)'";
        break;
    case "Куллер (доп)":
        $sql = "SELECT id, name FROM components WHERE category = 'Куллер (доп)'";
        break;
    default:
        echo json_encode(array()); // Если категория не найдена, возвращаем пустой массив
        exit();
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $components = array();
    while ($row = $result->fetch_assoc()) {
        $components[] = array(
            'id' => $row['id'],
            'name' => $row['name']
        );
    }
    echo json_encode($components);
} else {
    echo json_encode(array()); // Если нет результатов, возвращаем пустой массив
}

$conn->close();
?>
