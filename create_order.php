<?php
require 'config.php';  // Подключение файла конфигурации

// Создание соединения
$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Установка кодировки соединения
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получение данных из формы
    $computer_id = intval($_POST['computer_id']);
    $date = $_POST['date'];
    $name = $_POST['name'];
    $city = $_POST['city'];
    $delivery = $_POST['delivery'];
    $motherboard_id = intval($_POST['motherboard']);
    $processor_id = intval($_POST['processor']);
    $ram_id = intval($_POST['ram']);
    $gpu_id = intval($_POST['gpu']);
    $psu_id = intval($_POST['psu']);
    $ssd_id = intval($_POST['ssd']);
    $hdd_id = intval($_POST['hdd']);
    $case_id = intval($_POST['case']);
    $cpu_cooler_id = intval($_POST['cpu_cooler']);
    $extra_cooler_id = !empty($_POST['extra_cooler']) ? intval($_POST['extra_cooler']) : null;
    $additional = isset($_POST['additional']) ? $_POST['additional'] : '';  // Используем isset
    $additional_price = isset($_POST['additional_price']) ? floatval($_POST['additional_price']) : 0.0;  // Используем isset
    $total_price = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0.0;  // Используем isset

    // Считаем финальную цену с учетом наценки
    $sqlPrice = "SELECT markup FROM computers WHERE id = ?";
    $stmtPrice = $conn->prepare($sqlPrice);
    $stmtPrice->bind_param("i", $computer_id);
    $stmtPrice->execute();
    $stmtPrice->bind_result($markup);
    $stmtPrice->fetch();
    $stmtPrice->close();

    $final_price = $total_price + $markup + $additional_price;

    // Создание заказа
    $sql = "
        INSERT INTO orders (
            computer_id, date, name, city, delivery, 
            motherboard_id, processor_id, ram_id, gpu_id, psu_id,
            ssd_id, hdd_id, case_id, cpu_cooler_id, extra_cooler_id, 
            additional, additional_price, total_price
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, 
            ?, ?, ?
        )
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssiiiiiiiiisssd", 
        $computer_id, $date, $name, $city, $delivery, 
        $motherboard_id, $processor_id, $ram_id, $gpu_id, $psu_id,
        $ssd_id, $hdd_id, $case_id, $cpu_cooler_id, $extra_cooler_id,
        $additional, $additional_price, $final_price  // Используем $final_price
    );

    if ($stmt->execute()) {
        // Если заказ успешно создан, уменьшаем количество компонентов на складе

        // Список используемых компонентов
        $componentIds = [
            $motherboard_id,
            $processor_id,
            $ram_id,
            $gpu_id,
            $psu_id,
            $ssd_id,
            $hdd_id,
            $case_id,
            $cpu_cooler_id
        ];

        // Если дополнительный кулер выбран, добавляем его в массив
        if ($extra_cooler_id) {
            $componentIds[] = $extra_cooler_id;
        }

        // Обновление количества компонентов
        foreach ($componentIds as $componentId) {
            $updateSql = "UPDATE components SET quantity = quantity - 1 WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("i", $componentId);
            if (!$updateStmt->execute()) {
                echo "Error updating component quantity for ID: " . $componentId;
            }
            $updateStmt->close();
        }

        // Перенаправление на страницу подтверждения
        echo "Order successfully created!";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
