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

    // Кодируем сообщение для URL
    $message = urlencode($message);

    // Формируем URL
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage?chat_id={$chatId}&text={$message}";

    // Инициализируем cURL
    $ch = curl_init();

    // Устанавливаем параметры cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Исполняем запрос
    $response = curl_exec($ch);

    // Проверяем на ошибки
    if ($response === false) {
        error_log("Telegram API request failed: " . curl_error($ch));
    } else {
        error_log("Telegram API request successful: " . $response);
    }

    // Закрываем cURL сессию
    curl_close($ch);
}

// Пример использования функции
sendTelegramNotification("Новый заказ!");


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

    // Список используемых компонентов для расчета общей стоимости
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

    // Рассчитываем общую стоимость выбранных компонентов
    $total_price = calculateComponentTotal($componentIds, $conn);

    // Получаем наценку и базовую цену компьютера
    $sqlPrice = "SELECT base_price, markup FROM computers WHERE id = ?";
    $stmtPrice = $conn->prepare($sqlPrice);
    $stmtPrice->bind_param("i", $computer_id);
    $stmtPrice->execute();
    $stmtPrice->bind_result($base_price, $markup);
    $stmtPrice->fetch();
    $stmtPrice->close();

    // Рассчитываем финальную цену
    $final_price =  $markup + $total_price + $additional_price;

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

    // Проверяем и привязываем параметры
    if ($stmt) {
        $stmt->bind_param("issssiiiiiiiiisssd", 
            $computer_id, $date, $name, $city, $delivery, 
            $motherboard_id, $processor_id, $ram_id, $gpu_id, $psu_id,
            $ssd_id, $hdd_id, $case_id, $cpu_cooler_id, $extra_cooler_id,
            $additional, $additional_price, $final_price  // Используем $final_price
        );

        // Исполняем запрос
        if ($stmt->execute()) {
            // Если заказ успешно создан, уменьшаем количество компонентов на складе

            // Обновление количества компонентов
            foreach ($componentIds as $componentId) {
                if ($componentId) {
                    $updateSql = "UPDATE components SET quantity = quantity - 1 WHERE id = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bind_param("i", $componentId);
                    if (!$updateStmt->execute()) {
                        echo "Error updating component quantity for ID: " . $componentId;
                    }
                    $updateStmt->close();
                }
            }

            // Отправка уведомления в Telegram
            $message = "Новый заказ:\n\nНазвание: {$name}\nГород: {$city}\nОбщая цена: {$final_price}₽";
            sendTelegramNotification($message);

            // Перенаправление на страницу подтверждения
            echo "Order successfully created!";
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

$conn->close();
?>
