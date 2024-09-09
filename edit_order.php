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

// Получение данных о заказе по ID
$order = null;
if (isset($_REQUEST['id'])) {
    $id = intval($_REQUEST['id']);

    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
} else {
    die("Order ID is missing.");
}

// Получение всех компонентов с их ценами
$components = [];
$sql = "SELECT id, name, category, price FROM components";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $components[$row['category']][$row['id']] = $row;
    }
}

// Получение данных о компьютере из таблицы computers
$computerId = intval($order['computer_id']);
$computerData = null;

if ($computerId) {
    $sql = "SELECT base_price, markup FROM computers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $computerId);
    $stmt->execute();
    $stmt->bind_result($basePrice, $markup);
    $stmt->fetch();
    $computerData = ['base_price' => $basePrice, 'markup' => $markup];
    $stmt->close();
}

function calculateFinalPrice($componentIds, $components, $basePrice, $markup, $additionalPrice) {
    $totalPrice = $basePrice; // Начальная цена только базовая цена
    $totalPrice += $markup; // Добавляем наценку

    foreach ($componentIds as $componentId) {
        if (empty($componentId)) continue;
        foreach ($components as $category => $compList) {
            if (isset($compList[$componentId])) {
                $totalPrice += $compList[$componentId]['price'];
                break;
            }
        }
    }

    $totalPrice += $additionalPrice; // Добавляем цену дополнений

    return $totalPrice - $basePrice;
}

// Определяем ID выбранных компонентов
$selectedComponentIds = [
    'motherboard_id' => $order['motherboard_id'] ?? 0,
    'processor_id' => $order['processor_id'] ?? 0,
    'ram_id' => $order['ram_id'] ?? 0,
    'gpu_id' => $order['gpu_id'] ?? 0,
    'psu_id' => $order['psu_id'] ?? 0,
    'ssd_id' => $order['ssd_id'] ?? 0,
    'hdd_id' => $order['hdd_id'] ?? 0,
    'case_id' => $order['case_id'] ?? 0,
    'cpu_cooler_id' => $order['cpu_cooler_id'] ?? 0,
    'extra_cooler_id' => $order['extra_cooler_id'] ?? 0,
];

// Цена дополнений
$additionalPrice = floatval($order['additional_price'] ?? 0);
$markup = $computerData['markup'] ?? 0;
$basePrice = $computerData['base_price'] ?? 0;

// Пересчитываем итоговую цену
$finalPrice = calculateFinalPrice(array_values($selectedComponentIds), $components, $basePrice, $markup, $additionalPrice);

// Функция для генерации HTML опций
function generateOptions($components, $type, $selectedId) {
    $html = '<option value="">Select ' . htmlspecialchars($type) . '</option>';
    if (isset($components[$type])) {
        foreach ($components[$type] as $component) {
            $selected = ($component['id'] == $selectedId) ? ' selected' : '';
            $html .= "<option value=\"{$component['id']}\" data-price=\"{$component['price']}\"$selected>{$component['name']} - {$component['price']} руб</option>";
        }
    }
    return $html;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $date = $_POST['date'];
    $name = $_POST['name'];
    $city = $_POST['city'];
    $delivery = $_POST['delivery'];
    $additional = $_POST['additional'];
    $additionalPrice = floatval($_POST['additional_price']);
    
    // Получаем новые ID компонентов из POST
    $newComponentIds = [
        'motherboard_id' => intval($_POST['component_motherboard']),
        'processor_id' => intval($_POST['component_processor']),
        'ram_id' => intval($_POST['component_ram']),
        'gpu_id' => intval($_POST['component_gpu']),
        'psu_id' => intval($_POST['component_psu']),
        'ssd_id' => intval($_POST['component_ssd']),
        'hdd_id' => intval($_POST['component_hdd']),
        'case_id' => intval($_POST['component_case']),
        'cpu_cooler_id' => intval($_POST['component_cpu_cooler']),
        'extra_cooler_id' => intval($_POST['component_extra_cooler']),
    ];

    // Пересчитываем итоговую цену с новыми компонентами
    $finalPrice = calculateFinalPrice(array_values($newComponentIds), $components, $basePrice, $markup, $additionalPrice);

    $sql = "UPDATE orders SET date = ?, name = ?, city = ?, delivery = ?, additional = ?, additional_price = ?, total_price = ?, 
    motherboard_id = ?, processor_id = ?, ram_id = ?, gpu_id = ?, psu_id = ?, ssd_id = ?, hdd_id = ?, case_id = ?, cpu_cooler_id = ?, extra_cooler_id = ? 
    WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssdiiiiiiiiiiii", $date, $name, $city, $delivery, $additional, $additionalPrice, $finalPrice,
                   $newComponentIds['motherboard_id'], $newComponentIds['processor_id'], $newComponentIds['ram_id'],
                   $newComponentIds['gpu_id'], $newComponentIds['psu_id'], $newComponentIds['ssd_id'], 
                   $newComponentIds['hdd_id'], $newComponentIds['case_id'], $newComponentIds['cpu_cooler_id'], 
                   $newComponentIds['extra_cooler_id'], $id);

    if ($stmt->execute()) {
        echo "Order updated successfully.";
        header("Location: orders.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 1em;
            border: 1px solid #ccc;
            border-radius: 1em;
        }
        label {
            margin-top: 1em;
            display: block;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"] {
            width: 100%;
            padding: 0.7em;
            margin-top: 0.5em;
        }
        select {
            width: 100%;
            padding: 0.5em;
            margin-top: 0.5em;
        }
        textarea {
            width: 100%;
            padding: 0.7em;
            margin-top: 0.5em;
        }
        input[type="submit"] {
            margin-top: 1em;
            padding: 0.7em;
            border: none;
            border-radius: 0.5em;
            background: #007BFF;
            color: white;
            font-size: 1em;
        }
    </style>
</head>
<body>
    <h2>Edit Order</h2>
    <?php if ($order): ?>
        <form action="edit_order.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($order['id']); ?>">

            <label for="date">Date:</label>
            <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($order['date']); ?>" required>

            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($order['name']); ?>" required>

            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($order['city']); ?>" required>

            <label for="delivery">Delivery Method:</label>
            <select id="delivery" name="delivery" required>
                <option value="До города" <?php echo $order['delivery'] == 'До города' ? 'selected' : ''; ?>>До города</option>
                <option value="Самовывоз" <?php echo $order['delivery'] == 'Самовывоз' ? 'selected' : ''; ?>>Самовывоз</option>
            </select>

            <label for="motherboard">Motherboard:</label>
            <select id="motherboard" name="component_motherboard" required>
                <?php echo generateOptions($components, 'Материнская плата', $order['motherboard_id']); ?>
            </select>

            <label for="processor">Processor:</label>
            <select id="processor" name="component_processor" required>
                <?php echo generateOptions($components, 'Процессор', $order['processor_id']); ?>
            </select>

            <label for="ram">RAM:</label>
            <select id="ram" name="component_ram" required>
                <?php echo generateOptions($components, 'Оперативная память', $order['ram_id']); ?>
            </select>

            <label for="gpu">Graphics Card:</label>
            <select id="gpu" name="component_gpu" required>
                <?php echo generateOptions($components, 'Видеокарта', $order['gpu_id']); ?>
            </select>

            <label for="psu">Power Supply Unit:</label>
            <select id="psu" name="component_psu" required>
                <?php echo generateOptions($components, 'Блок питания', $order['psu_id']); ?>
            </select>

            <label for="ssd">SSD:</label>
            <select id="ssd" name="component_ssd">
                <?php echo generateOptions($components, 'SSD диск', $order['ssd_id']); ?>
            </select>

            <label for="hdd">HDD:</label>
            <select id="hdd" name="component_hdd">
                <?php echo generateOptions($components, 'HDD диск', $order['hdd_id']); ?>
            </select>

            <label for="case">Case:</label>
            <select id="case" name="component_case" required>
                <?php echo generateOptions($components, 'Корпус', $order['case_id']); ?>
            </select>

            <label for="cpu_cooler">CPU Cooler:</label>
            <select id="cpu_cooler" name="component_cpu_cooler">
                <?php echo generateOptions($components, 'Куллер (процессор)', $order['cpu_cooler_id']); ?>
            </select>

            <label for="extra_cooler">Extra Cooler:</label>
            <select id="extra_cooler" name="component_extra_cooler">
                <?php echo generateOptions($components, 'Дополнительный кулер', $order['extra_cooler_id']); ?>
            </select>

            <label for="additional">Additional Information:</label>
            <textarea id="additional" name="additional"><?php echo htmlspecialchars($order['additional']); ?></textarea>

            <label for="additional_price">Additional Price:</label>
            <input type="number" id="additional_price" name="additional_price" value="<?php echo htmlspecialchars($order['additional_price']); ?>" step="0.01" min="0">

            <label for="final_price">Final Price:</label>
            <input type="number" id="final_price" name="final_price" value="<?php echo htmlspecialchars($finalPrice); ?>" readonly>

            <input type="submit" value="Update Order">
        </form>
    <?php else: ?>
        <p>Order not found.</p>
    <?php endif; ?>
</body>
</html>
