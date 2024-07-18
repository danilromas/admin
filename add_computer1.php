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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const componentSelects = document.querySelectorAll("select[name]");
            const basePriceInput = document.getElementById("base_price");

            componentSelects.forEach(select => {
                select.addEventListener("change", updateBasePrice);
            });

            function updateBasePrice() {
                let totalPrice = 0;
                let remaining = componentSelects.length;

                componentSelects.forEach(select => {
                    const componentId = select.value;
                    if (componentId) {
                        fetch(`get_component_price.php?id=${componentId}`)
                            .then(response => response.json())
                            .then(data => {
                                totalPrice += parseFloat(data.price);
                                remaining--;
                                if (remaining === 0) {
                                    basePriceInput.value = totalPrice.toFixed(2);
                                }
                            })
                            .catch(error => console.error('Error:', error));
                    } else {
                        remaining--;
                        if (remaining === 0) {
                            basePriceInput.value = totalPrice.toFixed(2);
                        }
                    }
                });
            }
        });
    </script>
</head>
<body>
    <h2>Add Computer Build</h2>
    <form action="add_computer.php" method="POST" enctype="multipart/form-data">
        <label for="name">Build Name:</label>
        <input type="text" id="name" name="name" required>
        
        <label for="motherboard">Motherboard:</label>
        <select id="motherboard" name="motherboard" required>
            <!-- Опции для выбора материнской платы -->
            <?php echo generateOptions("Материнская плата"); ?>
        </select>
        
        <label for="processor">Processor:</label>
        <select id="processor" name="processor" required>
            <!-- Опции для выбора процессора -->
            <?php echo generateOptions("Процессор"); ?>
        </select>
        
        <label for="ram">RAM:</label>
        <select id="ram" name="ram" required>
            <!-- Опции для выбора оперативной памяти -->
            <?php echo generateOptions("Оперативная память"); ?>
        </select>
        
        <label for="gpu">Graphics Card:</label>
        <select id="gpu" name="gpu" required>
            <!-- Опции для выбора видеокарты -->
            <?php echo generateOptions("Видеокарта"); ?>
        </select>
        
        <label for="psu">Power Supply:</label>
        <select id="psu" name="psu" required>
            <!-- Опции для выбора блока питания -->
            <?php echo generateOptions("Блок питания"); ?>
        </select>
        
        <label for="ssd">SSD:</label>
        <select id="ssd" name="ssd" required>
            <!-- Опции для выбора SSD -->
            <?php echo generateOptions("SSD диск"); ?>
        </select>
        
        <label for="hdd">HDD:</label>
        <select id="hdd" name="hdd" required>
            <!-- Опции для выбора HDD -->
            <?php echo generateOptions("HDD диск"); ?>
        </select>
        
        <label for="case">Case:</label>
        <select id="case" name="case" required>
            <!-- Опции для выбора корпуса -->
            <?php echo generateOptions("Корпус"); ?>
        </select>
        
        <label for="cpu_cooler">CPU Cooler:</label>
        <select id="cpu_cooler" name="cpu_cooler" required>
            <!-- Опции для выбора кулера для процессора -->
            <?php echo generateOptions("Куллер (процессор)"); ?>
        </select>
        
        <label for="extra_cooler">Extra Cooler:</label>
        <select id="extra_cooler" name="extra_cooler" required>
            <!-- Опции для выбора дополнительного кулера -->
            <?php echo generateOptions("Куллер (доп)"); ?>
        </select>
        
        <label for="case_photo">Case Photo:</label>
        <input type="file" id="case_photo" name="case_photo" required>
        
        <label for="base_price">Base Price:</label>
        <input type="number" id="base_price" name="base_price" step="0.01" readonly required>
        
        <label for="markup">Markup:</label>
        <input type="number" id="markup" name="markup" step="0.01" required>
        <form action="add_computer.php" method="post">
        <select id="shop" name="shop" required>
            <option value="Tech Power">Tech Power</option>
            <option value="HQ">HQ</option>
            <option value="Artem">Artem</option>
            <option value="4">4</option>
        </select>
        
        <input type="submit" value="Add Computer Build">
    </form>

    <?php
    function generateOptions($category) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "computer_sales";

        // Создание соединения
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Установка кодировки
        if (!$conn->set_charset("utf8mb4")) {
            printf("Error loading character set utf8mb4: %s\n", $conn->error);
            exit();
        }

        // Проверка соединения
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Запрос к базе данных для выбранной категории
        $sql = "SELECT id, name FROM components WHERE category = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();

        // Вывод опций выбора
        $options = "";
        while ($row = $result->fetch_assoc()) {
            $options .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
        }

        // Закрытие подготовленного запроса и соединения
        $stmt->close();
        $conn->close();

        return $options;
    }
    ?>

</body>
</html>