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

// Установка заголовка кодировки
header('Content-Type: text/html; charset=utf-8');

$computer = [];
$components = [];

// Получение данных о компьютере, если запрос GET был выполнен
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
$sql = "SELECT id, name, category, price FROM components";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $components[$row['category']][] = $row;
    }
}

// Расчет базовой цены на основе выбранных компонентов
function calculateBasePrice($componentIds, $components) {
    $totalPrice = 0.0;
    foreach ($componentIds as $componentId) {
        if (empty($componentId)) continue; // Пропустить, если ID пустой
        foreach ($components as $category => $compList) {
            foreach ($compList as $component) {
                if ($component['id'] == $componentId) {
                    $totalPrice += $component['price'];
                    break 2; // Exit both foreach loops
                }
            }
        }
    }
    return $totalPrice;
}

// Определяем ID выбранных компонентов
$selectedComponentIds = [
    $computer['motherboard_id'],
    $computer['processor_id'],
    $computer['ram_id'],
    $computer['gpu_id'],
    $computer['psu_id'],
    $computer['ssd_id'],
    $computer['hdd_id'],
    $computer['case_id'],
    $computer['cpu_cooler_id'],
    $computer['extra_cooler_id'],
];

// Пересчитываем базовую цену
$basePrice = calculateBasePrice($selectedComponentIds, $components);

$conn->close();

// Функция для генерации HTML опций
function generateOptions($components, $type, $selectedId) {
    $html = '';
    if (isset($components[$type])) {
        foreach ($components[$type] as $component) {
            $selected = ($component['id'] == $selectedId) ? ' selected' : '';
            $html .= "<option value=\"{$component['id']}\"$selected>{$component['name']}</option>";
        }
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Computer Build</title>
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
        input[type="number"] {
            width: 100%;
            padding: 0.7em;
            margin-top: 0.5em;
        }
        select {
            width: 100%;
            padding: 0.5em;
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
            const basePriceInput = document.getElementById("base_price");
            const finalPriceInput = document.getElementById("final_price");
            const markupInput = document.getElementById("markup");

            componentSelects.forEach(select => {
                select.addEventListener("change", updateBasePrice);
            });

            basePriceInput.addEventListener("input", calculateMarkup);
            finalPriceInput.addEventListener("input", calculateMarkup);

            function updateBasePrice() {
                let totalPrice = 0;
                let remaining = componentSelects.length;

                componentSelects.forEach(select => {
                    const componentId = select.value;
                    if (componentId) {
                        fetch(`get_component_price1.php?id=${componentId}`)
                            .then(response => response.json())
                            .then(data => {
                                totalPrice += parseFloat(data.price);
                                remaining--;
                                if (remaining === 0) {
                                    basePriceInput.value = totalPrice.toFixed(2);
                                    calculateMarkup();
                                }
                            })
                            .catch(error => console.error('Error:', error));
                    } else {
                        remaining--;
                        if (remaining === 0) {
                            basePriceInput.value = totalPrice.toFixed(2);
                            calculateMarkup();
                        }
                    }
                });
            }

            function calculateMarkup() {
                const basePrice = parseFloat(basePriceInput.value) || 0;
                const finalPrice = parseFloat(finalPriceInput.value) || 0;
                const markup = finalPrice - basePrice;
                markupInput.value = markup.toFixed(2);
            }

            // Set initial base price and markup
            updateBasePrice();
        });
    </script>
</head>
<body>
    <h2>Edit Computer Build</h2>
    <form action="update_computer.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($computer['id'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="name">Build Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($computer['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
        
        <label for="motherboard">Motherboard:</label>
        <select id="motherboard" name="motherboard" required>
            <?php echo generateOptions($components, "Материнская плата", $computer['motherboard_id']); ?>
        </select>
        
        <label for="processor">Processor:</label>
        <select id="processor" name="processor" required>
            <?php echo generateOptions($components, "Процессор", $computer['processor_id']); ?>
        </select>
        
        <label for="ram">RAM:</label>
        <select id="ram" name="ram" required>
            <?php echo generateOptions($components, "Оперативная память", $computer['ram_id']); ?>
        </select>
        
        <label for="gpu">Graphics Card:</label>
        <select id="gpu" name="gpu" required>
            <?php echo generateOptions($components, "Видеокарта", $computer['gpu_id']); ?>
        </select>
        
        <label for="psu">Power Supply:</label>
        <select id="psu" name="psu" required>
            <?php echo generateOptions($components, "Блок питания", $computer['psu_id']); ?>
        </select>
        
        <label for="ssd">SSD:</label>
        <select id="ssd" name="ssd" required>
            <?php echo generateOptions($components, "SSD диск", $computer['ssd_id']); ?>
        </select>
        
        <label for="hdd">HDD:</label>
        <select id="hdd" name="hdd" required>
            <?php echo generateOptions($components, "HDD диск", $computer['hdd_id']); ?>
        </select>
        
        <label for="case">Case:</label>
        <select id="case" name="case" required>
            <?php echo generateOptions($components, "Корпус", $computer['case_id']); ?>
        </select>
        
        <label for="cpu_cooler">CPU Cooler:</label>
        <select id="cpu_cooler" name="cpu_cooler" required>
            <?php echo generateOptions($components, "Куллер (процессор)", $computer['cpu_cooler_id']); ?>
        </select>
        
        <label for="extra_cooler">Extra Cooler:</label>
        <select id="extra_cooler" name="extra_cooler">
            <option value="">Not Selected</option>
            <?php echo generateOptions($components, "Куллер (доп)", $computer['extra_cooler_id']); ?>
        </select>

        <label for="case_photo">Case Photo:</label>
<input type="file" id="case_photo" name="case_photo">
<?php if (!empty($computer['case_photo'])): ?>
    <div class="photo-preview">
        <label>Current Photo:</label>
        <p><?php echo htmlspecialchars(basename($computer['case_photo']), ENT_QUOTES, 'UTF-8'); ?></p>
        <img src="<?php echo htmlspecialchars(basename($computer['case_photo']), ENT_QUOTES, 'UTF-8'); ?>" alt="Case Photo" style="max-width: 100%; height: auto;">
    </div>
<?php endif; ?>

        <label for="base_price">Base Price:</label>
        <input type="number" id="base_price" name="base_price" step="0.01" value="<?php echo htmlspecialchars($basePrice, ENT_QUOTES, 'UTF-8'); ?>" readonly>

        <label for="markup">Markup:</label>
        <input type="number" id="markup" name="markup" step="0.01" value="0.00" readonly>

        <label for="final_price">Final Price:</label>
        <input type="number" id="final_price" name="final_price" step="0.01" value="<?php echo htmlspecialchars($computer['final_price'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <div class="form-group">
                <label for="shop">Shop:</label>
                <select id="shop" name="shop" required>
                    <option value="Tech Power">Tech Power</option>
                    <option value="HQ">HQ</option>
                    <option value="Artem">Artem</option>
                    <option value="4">4</option>
                </select>
            </div>

        <input type="submit" value="Update Computer Build">
    </form>
</body>
</html>
