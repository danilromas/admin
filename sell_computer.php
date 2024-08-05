<?php
require 'config.php';  // Подключение файла конфигурации

// Подключение к базе данных с установкой кодировки
$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$computer = null;
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM computers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $computer = $result->fetch_assoc();
    } else {
        echo "No records found for ID: " . htmlspecialchars($id) . "<br>";
    }

    $stmt->close();

    if ($computer) {
        // Список компонентов
        $components = [
            'motherboard' => $computer['motherboard_id'],
            'ram' => $computer['ram_id'],
            'gpu' => $computer['gpu_id'],
            'psu' => $computer['psu_id'],
            'ssd' => $computer['ssd_id'],
            'hdd' => $computer['hdd_id'],
            'case' => $computer['case_id']
        ];

        foreach ($components as $type => $id) {
            // Проверка наличия компонента
            $checkQuery = "SELECT quantity, name FROM components WHERE id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("i", $id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $component = $checkResult->fetch_assoc();

            if ($component['quantity'] > 0) {
                // Уменьшение количества компонентов
                $updateQuery = "UPDATE components SET quantity = quantity - 1 WHERE id = ? AND quantity > 0";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("i", $id);
                $updateStmt->execute();
                $updateStmt->close();
            } else {
                $errors[] = "Component " . htmlspecialchars($component['name']) . " is out of stock.";
            }
            $checkStmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
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
        textarea {
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
        .readonly {
            background-color: #e9ecef;
        }
        .errors {
            color: #DC3545;
            margin-bottom: 1em;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const additionalPriceInput = document.getElementById("additional_price");
            const totalPriceInput = document.getElementById("total_price");
            const basePriceInput = document.getElementById("base_price");

            function updateTotalPrice() {
                const basePrice = parseFloat(basePriceInput.value) || 0;
                const additionalPrice = parseFloat(additionalPriceInput.value) || 0;
                totalPriceInput.value = (basePrice + additionalPrice).toFixed(2);
            }

            additionalPriceInput.addEventListener("input", updateTotalPrice);

            // Initial total price calculation
            updateTotalPrice();
        });
    </script>
</head>
<body>
    <h2>Sell Computer</h2>
    <?php if ($computer): ?>
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form action="create_order.php" method="POST">
            <input type="hidden" name="computer_id" value="<?php echo htmlspecialchars($computer['id']); ?>">

            <label for="base_price">Base Price:</label>
            <input type="number" id="base_price" class="readonly" value="<?php echo htmlspecialchars($computer['final_price']); ?>" readonly>

            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>

            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="city">City:</label>
            <input type="text" id="city" name="city" required>

            <label for="delivery">Delivery Method:</label>
            <select id="delivery" name="delivery" required>
                <option value="До города">До города</option>
                <option value="Самовывоз">Самовывоз</option>
            </select>

            <label for="additional">Additional Components:</label>
            <textarea id="additional" name="additional"></textarea>

            <label for="additional_price">Additional Price:</label>
            <input type="number" id="additional_price" name="additional_price" step="0.01">

            <label for="total_price">Total Price:</label>
            <input type="number" id="total_price" name="total_price" step="0.01" readonly>

            <input type="submit" value="Confirm Order">
        </form>
    <?php else: ?>
        <p>Error: Computer not found.</p>
    <?php endif; ?>
</body>
</html>
