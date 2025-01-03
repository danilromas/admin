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

function calculateComponentTotal($componentIds, $conn) {
    $total = 0;
    $placeholders = implode(',', array_fill(0, count($componentIds), '?'));
    $types = str_repeat('i', count($componentIds));
    
    $sql = "SELECT SUM(price) FROM components WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$componentIds);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();

    return $total;
}

function sendTelegramNotification($message) {
    $botToken = '6811663386:AAFD--8cBLJjjac0maWmW_-7GcmXzV1B3to';  // Ваш токен бота
    $chatId = '-1002060916773';  // Ваш правильный chat_id

    $url = "https://api.telegram.org/bot{$botToken}/sendMessage?chat_id={$chatId}&text=" . urlencode($message);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if ($response === false) {
        error_log("Telegram API request failed: " . curl_error($ch));
    }
    curl_close($ch);
}

// Пример использования функции
sendTelegramNotification("Новый заказ!");

function sendTelegramNotification1($message) {
    $botToken = '6811663386:AAFD--8cBLJjjac0maWmW_-7GcmXzV1B3to';  // Ваш токен бота
    $chatId = '-4574327227';  // Ваш правильный chat_id

    $url = "https://api.telegram.org/bot{$botToken}/sendMessage?chat_id={$chatId}&text=" . urlencode($message);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if ($response === false) {
        error_log("Telegram API request failed: " . curl_error($ch));
    }
    curl_close($ch);
}

sendTelegramNotification1("Новый заказ!");

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
    $additional = isset($_POST['additional']) ? $_POST['additional'] : '';
    $additional_price = isset($_POST['additional_price']) ? floatval($_POST['additional_price']) : 0.0;

    // Получение названий компонентов
    function getComponentName($id, $conn) {
        $sql = "SELECT name FROM components WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($name);
        $stmt->fetch();
        $stmt->close();
        return $name;
    }

    // Получение названия ПК
    function getComputerName($computer_id, $conn) {
        $sql = "SELECT name FROM computers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $computer_id);
        $stmt->execute();
        $stmt->bind_result($name);
        $stmt->fetch();
        $stmt->close();
        return $name;
    }

    // Получение названий компонентов
    $motherboard_name = getComponentName($motherboard_id, $conn);
    $processor_name = getComponentName($processor_id, $conn);
    $ram_name = getComponentName($ram_id, $conn);
    $gpu_name = getComponentName($gpu_id, $conn);
    $psu_name = getComponentName($psu_id, $conn);
    $ssd_name = getComponentName($ssd_id, $conn);
    $hdd_name = getComponentName($hdd_id, $conn);
    $case_name = getComponentName($case_id, $conn);
    $cpu_cooler_name = getComponentName($cpu_cooler_id, $conn);
    $extra_cooler_name = $extra_cooler_id !== null ? getComponentName($extra_cooler_id, $conn) : null;

    // Получаем название ПК
    $computer_name = getComputerName($computer_id, $conn);

    // Список компонентов для расчета общей стоимости
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

    if ($extra_cooler_id !== null) {
        $componentIds[] = $extra_cooler_id;
    }

    // Рассчитываем общую стоимость компонентов
    $total_price = calculateComponentTotal($componentIds, $conn);

    // Получаем базовую цену и наценку
    $sqlPrice = "SELECT base_price, markup FROM computers WHERE id = ?";
    $stmtPrice = $conn->prepare($sqlPrice);
    $stmtPrice->bind_param("i", $computer_id);
    $stmtPrice->execute();
    $stmtPrice->bind_result($base_price, $markup);
    $stmtPrice->fetch();
    $stmtPrice->close();

    // Финальная цена
    $final_price = $markup + $total_price + $additional_price;

    // Создаем заказ
    $sql = "
        INSERT INTO orders (
            computer_id, date, name, city, delivery, 
            motherboard_id, processor_id, ram_id, gpu_id, psu_id,
            ssd_id, hdd_id, case_id, cpu_cooler_id, extra_cooler_id, 
            additional, additional_price, total_price, status
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, 
            ?, ?, ?, 'принят'
        )
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isssiiiiiiiiisiidd",
        $computer_id, $date, $name, $city, $delivery,
        $motherboard_id, $processor_id, $ram_id, $gpu_id, $psu_id,
        $ssd_id, $hdd_id, $case_id, $cpu_cooler_id, $extra_cooler_id,
        $additional, $additional_price, $final_price
    );

    if ($stmt->execute()) {
        // Формируем характеристики для сообщения
        $characteristics = "Материнская плата: {$motherboard_name}\n" . 
                           "Процессор: {$processor_name}\n" . 
                           "ОЗУ: {$ram_name}\n" . 
                           "Видеокарта: {$gpu_name}\n" . 
                           "Блок питания: {$psu_name}\n" . 
                           "SSD: {$ssd_name}\n" . 
                           "HDD: {$hdd_name}\n" . 
                           "Корпус: {$case_name}\n" . 
                           "Охладитель процессора: {$cpu_cooler_name}\n";

        if ($extra_cooler_name !== null) {
            $characteristics .= "Доп. охладитель: {$extra_cooler_name}\n";
        }

        // Формируем сообщение
        $message = "Новый заказ:\n\n" . 
                   "Название ПК: {$computer_name}\n" .  // Выводим название ПК
                   "Имя клиента: {$name}\n" . 
                   "Город: {$city}\n" . 
                   "Доставка: {$delivery}\n" . 
                   "Дополнительно: {$additional}\n" . 
                   "Общая цена: {$final_price}₽\n\n" . 
                   "Характеристики:\n{$characteristics}";

        // Отправляем уведомления
        sendTelegramNotification($message);
        sendTelegramNotification1($message);

        // Подтверждение успешного заказа
        echo "Order successfully created!";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
