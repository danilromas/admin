<?php
require 'config.php';  // Подключение файла конфигурации

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение данных из формы
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $motherboard = intval($_POST['motherboard']);
    $processor = intval($_POST['processor']);
    $ram = intval($_POST['ram']);
    $gpu = intval($_POST['gpu']);
    $psu = intval($_POST['psu']);
    $ssd = intval($_POST['ssd']);
    $hdd = intval($_POST['hdd']);
    $case = intval($_POST['case']);
    $cpu_cooler = intval($_POST['cpu_cooler']);
    $extra_cooler = intval($_POST['extra_cooler']);
    $base_price = floatval($_POST['base_price']);
    $markup = floatval($_POST['markup']);
    $final_price = floatval($_POST['final_price']);
    $shop = $_POST['shop'];

    // Создание соединения
    $conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Проверяем, было ли загружено новое фото
    $photoPath = null;
    if (isset($_FILES['case_photo']) && $_FILES['case_photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $uploadFile = $uploadDir . basename($_FILES['case_photo']['name']);
        $uploadFile1 = basename($_FILES['case_photo']['name']);
        if (move_uploaded_file($_FILES['case_photo']['tmp_name'], $uploadFile)) {
            $photoPath = $uploadFile1;
        }
    } else {
        // Если новое фото не загружено, получаем текущее значение из базы данных
        $sql = "SELECT case_photo FROM computers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($currentPhoto);
        $stmt->fetch();
        $stmt->close();

        $photoPath = $currentPhoto; // Сохраняем текущее фото
    }

    // Подготовка SQL-запроса для обновления
    $sql = "UPDATE computers SET 
        name = ?, 
        motherboard_id = ?, 
        processor_id = ?, 
        ram_id = ?, 
        gpu_id = ?, 
        psu_id = ?, 
        ssd_id = ?, 
        hdd_id = ?, 
        case_id = ?, 
        cpu_cooler_id = ?, 
        extra_cooler_id = ?, 
        case_photo = ?, 
        base_price = ?, 
        markup = ?, 
        final_price = ?, 
        shop = ? 
        WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    // Привязка параметров
    $stmt->bind_param(
        "siiiiiiiiissddssi", 
        $name, $motherboard, $processor, $ram, $gpu, $psu, $ssd, $hdd, $case, 
        $cpu_cooler, $extra_cooler, $photoPath, 
        $base_price, $markup, $final_price, $shop, $id
    );

    // Выполнение запроса
    if ($stmt->execute()) {
        echo "Computer build updated successfully.";
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    // Закрытие соединения
    $stmt->close();
    $conn->close();
} else {
    die("Invalid request.");
}
?>
