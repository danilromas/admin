<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "computer_sales";

// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$computer = [];

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

function generateOptions($conn, $type, $selectedId) {
    $tableMap = [
        "Материнская плата" => "motherboards",
        "Процессор" => "processors",
        "Оперативная память" => "ram",
        "Видеокарта" => "gpus",
        "Блок питания" => "psus",
        "SSD диск" => "ssds",
        "HDD диск" => "hdds",
        "Корпус" => "cases",
        "Куллер (процессор)" => "cpu_coolers",
        "Куллер (доп)" => "extra_coolers"
    ];

    if (!isset($tableMap[$type])) {
        return ''; // Неверный тип компонента
    }

    $table = $tableMap[$type];

    $sql = "SELECT id, name FROM $table";
    $result = $conn->query($sql);

    if (!$result) {
        return ''; // Ошибка выполнения запроса
    }

    $html = '';
    while ($row = $result->fetch_assoc()) {
        $selected = ($row['id'] == $selectedId) ? ' selected' : '';
        $html .= "<option value=\"{$row['id']}\"$selected>{$row['name']}</option>";
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
    </style>
</head>
<body>
    <h2>Edit Computer Build</h2>
    <form action="update_computer.php" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($computer['id']); ?>">

        <label for="name">Build Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($computer['name']); ?>" required>
        
        <label for="motherboard">Motherboard:</label>
        <select id="motherboard" name="motherboard" required>
            <?php echo generateOptions($conn, "Материнская плата", $computer['motherboard_id']); ?>
        </select>
        
        <label for="processor">Processor:</label>
        <select id="processor" name="processor" required>
            <?php echo generateOptions($conn, "Процессор", $computer['processor_id']); ?>
        </select>
        
        <label for="ram">RAM:</label>
        <select id="ram" name="ram" required>
            <?php echo generateOptions($conn, "Оперативная память", $computer['ram_id']); ?>
        </select>
        
        <label for="gpu">Graphics Card:</label>
        <select id="gpu" name="gpu" required>
            <?php echo generateOptions($conn, "Видеокарта", $computer['gpu_id']); ?>
        </select>
        
        <label for="psu">Power Supply:</label>
        <select id="psu" name="psu" required>
            <?php echo generateOptions($conn, "Блок питания", $computer['psu_id']); ?>
        </select>
        
        <label for="ssd">SSD:</label>
        <select id="ssd" name="ssd" required>
            <?php echo generateOptions($conn, "SSD диск", $computer['ssd_id']); ?>
        </select>
        
        <label for="hdd">HDD:</label>
        <select id="hdd" name="hdd" required>
            <?php echo generateOptions($conn, "HDD диск", $computer['hdd_id']); ?>
        </select>
        
        <label for="case">Case:</label>
        <select id="case" name="case" required>
            <?php echo generateOptions($conn, "Корпус", $computer['case_id']); ?>
        </select>
        
        <label for="cpu_cooler">CPU Cooler:</label>
        <select id="cpu_cooler" name="cpu_cooler" required>
            <?php echo generateOptions($conn, "Куллер (процессор)", $computer['cpu_cooler_id']); ?>
        </select>
        
        <label for="extra_cooler">Extra Cooler:</label>
        <select id="extra_cooler" name="extra_cooler" required>
            <?php echo generateOptions($conn, "Куллер (доп)", $computer['extra_cooler_id']); ?>
        </select>
        
        <label for="base_price">Base Price:</label>
        <input type="number" id="base_price" name="base_price" value="<?php echo htmlspecialchars($computer['base_price']); ?>" step="0.01" required>
        
        <label for="markup">Markup:</label>
        <input type="number" id="markup" name="markup" value="<?php echo htmlspecialchars($computer['markup']); ?>" step="0.01" required>

        <label for="shop">Shop:</label>
        <select id="shop" name="shop" required>
            <option value="Tech Power" <?php echo $computer['shop'] == 'Tech Power' ? 'selected' : ''; ?>>Tech Power</option>
            <option value="HQ" <?php echo $computer['shop'] == 'HQ' ? 'selected' : ''; ?>>HQ</option>
            <option value="Artem" <?php echo $computer['shop'] == 'Artem' ? 'selected' : ''; ?>>Artem</option>
            <option value="4" <?php echo $computer['shop'] == '4' ? 'selected' : ''; ?>>4</option>
        </select>
        
        <input type="submit" value="Update Computer Build">
    </form>
</body>
</html>
