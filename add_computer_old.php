<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Computer Build</title>
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
        input[type="file"] {
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
    <h2>Add Computer Build</h2>
    <form action="add_computer.php" method="POST" enctype="multipart/form-data">
        <label for="name">Build Name:</label>
        <input type="text" id="name" name="name" required>
        
        <!-- Select для Материнской платы -->
        <label for="motherboard">Motherboard:</label>
        <select id="motherboard" name="motherboard" required>
            <?php
            require 'config.php';  // Подключение файла конфигурации


            // Создание соединения
            $conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

            // Установка кодировки
            if (!$conn->set_charset("utf8mb4")) {
                printf("Error loading character set utf8mb4: %s\n", $conn->error);
                exit();
            }

            // Проверка соединения
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Запрос к базе данных для выбора материнских плат
            $category = "Материнская плата";
            $sql = "SELECT id, name FROM components WHERE category = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();

            // Вывод опций выбора
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
            }

            // Закрытие подготовленного запроса и соединения
            $stmt->close();
            $conn->close();
            ?>
        </select>
        
        <!-- Select для Оперативной памяти -->
        <label for="ram">RAM:</label>
        <select id="ram" name="ram" required>
            <?php
            $conn = new mysqli($servername, $username, $password, $dbname);
            if (!$conn->set_charset("utf8mb4")) {
                printf("Error loading character set utf8mb4: %s\n", $conn->error);
                exit();
            }
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            $category = "Оперативная память";
            $sql = "SELECT id, name FROM components WHERE category = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
            }
            $stmt->close();
            $conn->close();
            ?>
        </select>
        
        <!-- Аналогично для остальных категорий (Процессор, Видеокарта, Блок питания и т.д.) -->

        <label for="gpu">Graphics Card:</label>
        <select id="gpu" name="gpu" required>
            <!-- Опции для выбора видеокарты будут добавлены динамически -->
        </select>
        
        <label for="psu">Power Supply:</label>
        <select id="psu" name="psu" required>
            <!-- Опции для выбора блока питания будут добавлены динамически -->
        </select>
        
        <label for="ssd">SSD:</label>
        <select id="ssd" name="ssd" required>
            <!-- Опции для выбора SSD будут добавлены динамически -->
        </select>
        
        <label for="hdd">HDD:</label>
        <select id="hdd" name="hdd" required>
            <!-- Опции для выбора HDD будут добавлены динамически -->
        </select>
        
        <label for="case">Case:</label>
        <select id="case" name="case" required>
            <!-- Опции для выбора корпуса будут добавлены динамически -->
        </select>
        
        <label for="cpu_cooler">CPU Cooler:</label>
        <select id="cpu_cooler" name="cpu_cooler" required>
            <!-- Опции для выбора кулера для процессора будут добавлены динамически -->
        </select>
        
        <label for="extra_cooler">Extra Cooler:</label>
        <select id="extra_cooler" name="extra_cooler" required>
            <!-- Опции для выбора дополнительного кулера будут добавлены динамически -->
        </select>
        
        <label for="case_photo">Case Photo:</label>
        <input type="file" id="case_photo" name="case_photo">
        
        <label for="base_price">Base Price:</label>
        <input type="number" id="base_price" name="base_price" step="0.01" required>
        
        <label for="markup">Markup:</label>
        <input type="number" id="markup" name="markup" step="0.01" required>
        
        <input type="submit" value="Add Computer Build">
    </form>
</body>
</html>