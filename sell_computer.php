<?php
require 'auth.php';
require 'config.php';  // Подключение файла конфигурации

check_login(); // Проверяет, авторизован ли пользователь

// Создание соединения
$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$is_admin = check_role(['admin']); // Проверяет, имеет ли пользователь роль администратора

// Установка кодировки соединения
$conn->set_charset("utf8mb4");

// Получение данных о компьютере по ID
$computer = null;
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "SELECT * FROM computers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $computer = $result->fetch_assoc();
    $stmt->close();
} else {
    die("Invalid request.");
}

// Получение всех компонентов с их ценами
$components = [];
$sql = "SELECT id, name, category, price, quantity FROM components";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $components[$row['category']][$row['id']] = $row;
    }
}

// Расчет итоговой цены на основе выбранных компонентов и добавления markup
function calculateFinalPrice($componentIds, $components, $markup, $additionalPrice) {
    $totalPrice = 0.0;
    foreach ($componentIds as $componentId) {
        if (empty($componentId)) continue; // Пропустить, если ID пустой
        foreach ($components as $category => $compList) {
            if (isset($compList[$componentId])) {
                $totalPrice += $compList[$componentId]['price'];
                break; // Выйти из текущего цикла
            }
        }
    }
    return $totalPrice + $markup + $additionalPrice;
}

// Определяем ID выбранных компонентов
$selectedComponentIds = [
    $computer['motherboard_id'] ?? 0,
    $computer['processor_id'] ?? 0,
    $computer['ram_id'] ?? 0,
    $computer['gpu_id'] ?? 0,
    $computer['psu_id'] ?? 0,
    $computer['ssd_id'] ?? 0,
    $computer['hdd_id'] ?? 0,
    $computer['case_id'] ?? 0,
    $computer['cpu_cooler_id'] ?? 0,
    $computer['extra_cooler_id'] ?? 0,
];

// Добавляем markup и цену дополнений к финальной цене
$markup = floatval($computer['markup'] ?? 0);
$additionalPrice = floatval($computer['additional_price'] ?? 0); // Предполагаем, что цена дополнений хранится в этой колонке

// Пересчитываем итоговую цену
$finalPrice = calculateFinalPrice($selectedComponentIds, $components, $markup, $additionalPrice);

// Функция для генерации HTML опций с проверкой наличия компонента
function generateOptions($components, $type, $selectedId, $is_admin = false) {
    // Выполняем проверку роли админа до вызова функции
    if (empty($is_admin)) {
        $is_admin = check_role(['admin']);
    }
    
    $html = '';
    if (isset($components[$type])) {
        foreach ($components[$type] as $component) {
            $selected = ($component['id'] == $selectedId) ? ' selected' : '';
            $availability = ($component['quantity'] == 0) ? ' (нет в наличии)' : '';
            $price = $is_admin ? " - {$component['price']} руб" : ''; // Цена только для администратора
            $html .= "<option value=\"{$component['id']}\"$selected>{$component['name']}$price$availability</option>";
        }
    }
    return $html;
}


$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sell Computer</title>
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
        .errors {
            color: #DC3545;
            margin-bottom: 1em;
        }
        .photo-preview {
            margin-top: 1em;
        }
        .photo-preview img {
            max-width: 100%;
            height: auto;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const componentSelects = document.querySelectorAll("select[name]");
            const finalPriceInput = document.getElementById("final_price");

            function updateFinalPrice() {
                let totalPrice = 0;
                componentSelects.forEach(select => {
                    const componentId = select.value;
                    if (componentId) {
                        fetch(`get_component_price.php?id=${componentId}`)
                            .then(response => response.json())
                            .then(data => {
                                totalPrice += parseFloat(data.price);
                                const markup = <?php echo $markup; ?>;
                                const additionalPrice = <?php echo $additionalPrice; ?>;
                                finalPriceInput.value = (totalPrice + markup + additionalPrice).toFixed(2);
                            })
                            .catch(error => console.error('Error:', error));
                    }
                });
            }

            // Обновление итоговой цены при изменении компонентов
            componentSelects.forEach(select => {
                select.addEventListener("change", updateFinalPrice);
            });

            // Первоначальный расчет итоговой цены
            updateFinalPrice();
        });
    </script>
</head>
<body>
    <h2>Sell Computer</h2>
    <?php if ($computer): ?>
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form action="create_order.php" method="POST">
            <input type="hidden" name="computer_id" value="<?php echo htmlspecialchars($computer['id']); ?>">

            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>

            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="city">City:</label>
            <input type="text" id="city" name="city" required>

            <label for="delivery">Delivery Method:</label>
            <select id="delivery" name="delivery" required>
                <option value="До города" <?php if (isset($computer['delivery_method']) && $computer['delivery_method'] == 'До города') echo 'selected'; ?>>До города</option>
                <option value="Самовывоз" <?php if (isset($computer['delivery_method']) && $computer['delivery_method'] == 'Самовывоз') echo 'selected'; ?>>Самовывоз</option>
            </select>

            <label for="motherboard">Motherboard:</label>
            <select id="motherboard" name="motherboard" required>
                <?php echo generateOptions($components, 'Материнская плата', $computer['motherboard_id']); ?>
            </select>

            <label for="processor">Processor:</label>
            <select id="processor" name="processor" required>
                <?php echo generateOptions($components, 'Процессор', $computer['processor_id']); ?>
            </select>

            <label for="ram">RAM:</label>
            <select id="ram" name="ram" required>
                <?php echo generateOptions($components, 'Оперативная память', $computer['ram_id']); ?>
            </select>

            <label for="gpu">Graphics Card:</label>
            <select id="gpu" name="gpu" required>
                <?php echo generateOptions($components, 'Видеокарта', $computer['gpu_id']); ?>
            </select>

            <label for="psu">Power Supply:</label>
            <select id="psu" name="psu" required>
                <?php echo generateOptions($components, 'Блок питания', $computer['psu_id']); ?>
            </select>

            <label for="ssd">SSD:</label>
            <select id="ssd" name="ssd" required>
                <?php echo generateOptions($components, 'SSD диск', $computer['ssd_id']); ?>
            </select>

            <label for="hdd">HDD:</label>
            <select id="hdd" name="hdd" required>
                <?php echo generateOptions($components, 'HDD диск', $computer['hdd_id']); ?>
            </select>

            <label for="case">Case:</label>
            <select id="case" name="case" required>
                <?php echo generateOptions($components, 'Корпус', $computer['case_id']); ?>
            </select>

            <label for="cpu_cooler">CPU Cooler:</label>
            <select id="cpu_cooler" name="cpu_cooler" required>
                <?php echo generateOptions($components, 'Куллер (процессор)', $computer['cpu_cooler_id']); ?>
            </select>

            <label for="extra_cooler">Extra Cooler:</label>
            <select id="extra_cooler" name="extra_cooler" required>
                <option value="">Not Selected</option>
                    <?php 
                        // Выводим опции для extra_cooler
                        if (!empty($components['Куллер (доп)'])) {
                            echo generateOptions($components, 'Куллер (доп)', $computer['extra_cooler_id'], $is_admin); 
                        } else {
                            echo '<option value="">No Coolers Available</option>';
                        }
                    ?>
            </select>

            <label for="additional">Additional Components:</label>
            <textarea id="additional" name="additional"><?php echo htmlspecialchars($computer['additional'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

            <label for="additional_price">Additional Price:</label>
            <input type="number" id="additional_price" name="additional_price" value="<?php echo htmlspecialchars($computer['additional_price'] ?? '0'); ?>" step="0.01">

            <label for="final_price">Final Price:</label>
            <input type="number" id="final_price" name="final_price" value="<?php echo htmlspecialchars($finalPrice); ?>" step="0.01" readonly>

            <input type="submit" value="Confirm Order">
        </form>
    <?php else: ?>
        <p>Computer not found.</p>
    <?php endif; ?>
</body>
</html>