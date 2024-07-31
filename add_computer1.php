<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Computer Build</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 1em;
            color: #555;
        }
        input[type="text"],
        input[type="number"],
        input[type="file"],
        select {
            width: 100%;
            padding: 0.8em;
            margin-top: 0.5em;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
        }
        input[type="submit"] {
            margin-top: 1.5em;
            padding: 1em;
            border: none;
            border-radius: 5px;
            background: #28a745;
            color: white;
            font-size: 1.2em;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        input[type="submit"]:hover {
            background: #218838;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group-inline {
            display: flex;
            justify-content: space-between;
        }
        .form-group-inline > div {
            flex: 1;
            margin-right: 10px;
        }
        .form-group-inline > div:last-child {
            margin-right: 0;
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
                        fetch(`get_component_price.php?id=${componentId}`)
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
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>Add Computer Build</h2>
        <form action="add_computer.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Build Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="motherboard">Motherboard:</label>
                <select id="motherboard" name="motherboard" required>
                    <?php echo generateOptions("Материнская плата"); ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="processor">Processor:</label>
                <select id="processor" name="processor" required>
                    <?php echo generateOptions("Процессор"); ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="ram">RAM:</label>
                <select id="ram" name="ram" required>
                    <?php echo generateOptions("Оперативная память"); ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="gpu">Graphics Card:</label>
                <select id="gpu" name="gpu" required>
                    <?php echo generateOptions("Видеокарта"); ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="psu">Power Supply:</label>
                <select id="psu" name="psu" required>
                    <?php echo generateOptions("Блок питания"); ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="ssd">SSD:</label>
                <select id="ssd" name="ssd" required>
                    <?php echo generateOptions("SSD диск"); ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="hdd">HDD:</label>
                <select id="hdd" name="hdd" required>
                    <?php echo generateOptions("HDD диск"); ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="case">Case:</label>
                <select id="case" name="case" required>
                    <?php echo generateOptions("Корпус"); ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="cpu_cooler">CPU Cooler:</label>
                <select id="cpu_cooler" name="cpu_cooler" required>
                    <?php echo generateOptions("Куллер (процессор)"); ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="extra_cooler">Extra Cooler:</label>
                <select id="extra_cooler" name="extra_cooler" required>
                    <?php echo generateOptions("Куллер (доп)"); ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="case_photo">Case Photo:</label>
                <input type="file" id="case_photo" name="case_photo" required>
            </div>
            
            <div class="form-group-inline">
                <div class="form-group">
                    <label for="base_price">Base Price:</label>
                    <input type="number" id="base_price" name="base_price" step="0.01" readonly required>
                </div>
                <div class="form-group">
                    <label for="final_price">Final Price:</label>
                    <input type="number" id="final_price" name="final_price" step="0.01" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="markup">Markup:</label>
                <input type="number" id="markup" name="markup" step="0.01" readonly required>
            </div>
            
            <div class="form-group">
                <label for="shop">Shop:</label>
                <select id="shop" name="shop" required>
                    <option value="Tech Power">Tech Power</option>
                    <option value="HQ">HQ</option>
                    <option value="Artem">Artem</option>
                    <option value="4">4</option>
                </select>
            </div>
            
            <input type="submit" value="Add Computer Build">
        </form>

        <?php
        function generateOptions($category) {
            require 'config.php';  // Подключение файла конфигурации


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
    </div>
</body>
</html>
