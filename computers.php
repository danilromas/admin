<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>List of Computers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .shop-section {
            width: 80%;
            max-width: 1200px;
            margin: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .shop-section h2 {
            font-size: 24px;
            margin-bottom: 20px;
            border-bottom: 2px solid #007BFF;
            padding-bottom: 10px;
            color: #333;
        }
        .computer-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .computer {
            width: 30%;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            text-align: center;
            background: linear-gradient(to bottom right, #f9f9f9, #ffffff);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .computer:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .computer img {
            width: 100%;
            height: 200px; /* Fixed height */
            object-fit: cover; /* Ensure image fits within the container */
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .computer h3 {
            font-size: 22px;
            margin-bottom: 10px;
            color: #333;
        }
        .computer ul {
            list-style-type: none;
            padding: 0;
            text-align: left;
        }
        .computer ul li {
            font-size: 16px;
            margin-bottom: 5px;
            color: #666;
        }
        .computer .price {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #007BFF;
        }
        .computer .availability {
            font-size: 16px;
            color: #28A745;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .computer .buttons {
            margin-top: 15px;
        }
        .computer .buttons form {
            display: inline-block;
        }
        .computer .buttons button {
            padding: 8px 15px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.2s, transform 0.2s;
        }
        .computer .buttons button:hover {
            transform: scale(1.05);
        }
        .edit-button {
            background-color: #007BFF;
            color: white;
        }
        .edit-button:hover {
            background-color: #0056b3;
        }
        .delete-button {
            background-color: #DC3545;
            color: white;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
        .sell_computer {
            background-color: #28A745;
            color: white;
        }
        .sell_computer:hover {
            background-color: #218838;
        }
        .back-button {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #f4f4f4;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 0.5em 1em;
            color: #007BFF;
            text-decoration: none;
            font-weight: bold;
        }
        .back-button:hover {
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
<a href="index.html" class="back-button">Back to Index</a>
    <?php
    require 'config.php';  // Подключение файла конфигурации

    // Создание соединения
    $conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

    // Проверка соединения
    $conn->set_charset("utf8mb4");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Запрос для получения уникальных магазинов
    $shopsQuery = "SELECT DISTINCT shop FROM computers ORDER BY shop";
    $shopsResult = $conn->query($shopsQuery);

    if ($shopsResult->num_rows > 0) {
        while ($shopRow = $shopsResult->fetch_assoc()) {
            $shopName = $shopRow['shop'];

            // Начало нового блока магазина
            echo '<div class="shop-section">';
            echo '<h2>' . htmlspecialchars($shopName) . '</h2>';
            echo '<div class="computer-list">'; // Добавлено

            // Запрос для получения компьютеров конкретного магазина
            $computersQuery = "SELECT * FROM computers WHERE shop = ? ORDER BY name";
            $computersStmt = $conn->prepare($computersQuery);
            $computersStmt->bind_param("s", $shopName);
            $computersStmt->execute();
            $computersResult = $computersStmt->get_result();

            if ($computersResult->num_rows > 0) {
                while ($computerRow = $computersResult->fetch_assoc()) {
                    $casePhoto = htmlspecialchars($computerRow['case_photo']);
                    // Проверка и добавление расширения только если его нет
                    $photoPath = 'uploads/' . $casePhoto;
                    if (pathinfo($photoPath, PATHINFO_EXTENSION) === '') {
                        $photoPath .= '.jpg';
                    }
            
                    echo '<div class="computer">';
                    echo '<img src="' . $photoPath . '" alt="Computer Photo">';
                    echo '<h3>' . htmlspecialchars($computerRow['name']) . '</h3>';
            
                    // Вывод компонентов
                    echo '<ul>';
                    $components = [
                        'Motherboard' => $computerRow['motherboard_id'],
                        'RAM' => $computerRow['ram_id'],
                        'GPU' => $computerRow['gpu_id'],
                        'PSU' => $computerRow['psu_id'],
                        'SSD' => $computerRow['ssd_id'],
                        'HDD' => $computerRow['hdd_id'],
                        'Case' => $computerRow['case_id']
                    ];

            

                    foreach ($components as $type => $id) {
                        $query = "SELECT name, price FROM components WHERE id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $component = $result->fetch_assoc();
                        if ($component) {
                            echo '<li><strong>' . htmlspecialchars($type) . ':</strong> ' . htmlspecialchars($component['name']) . ' - ' . htmlspecialchars($component['price']) . ' руб. </li>';
                        } else {
                            echo '<li><strong>' . htmlspecialchars($type) . ':</strong> Unknown</li>';
                        }
                    }
                    echo '</ul>';

                    echo '<p class="price">Cost Price: ' . htmlspecialchars(number_format($computerRow['base_price'], 2)) . ' руб.</p>';
                    echo '<p class="price">Final Price: ' . htmlspecialchars($computerRow['final_price']) . ' руб.</p>';
                    // Доступность

                    // Кнопки редактирования, удаления и продажи
                    echo '<div class="buttons">';
                    echo '<form action="edit_computer.php" method="get">';
                    echo '<input type="hidden" name="id" value="' . htmlspecialchars($computerRow['id']) . '">';
                    echo '<button type="submit" class="edit-button">Edit</button>';
                    echo '</form>';

                    echo '<form action="delete_computer.php" method="post">';
                    echo '<input type="hidden" name="id" value="' . htmlspecialchars($computerRow['id']) . '">';
                    /*echo '<button type="submit" class="delete-button">Delete</button>'; */
                    echo '</form>';

                    echo '<form action="sell_computer.php" method="get">';
                    echo '<input type="hidden" name="id" value="' . htmlspecialchars($computerRow['id']) . '">';
                    echo '<button type="submit" class="sell_computer">Sell</button>';
                    echo '</form>';
                    echo '</div>'; // Закрытие блока кнопок

                    echo '</div>'; // Закрытие блока компьютера
                }
            } else {
                echo "<p>No computers found in this shop.</p>";
            }

            echo '</div>'; // Закрытие computer-list
            echo '</div>'; // Закрытие блока магазина
        }
    } else {
        echo "<p>No shops found.</p>";
    }

    $conn->close();
    ?>

</body>
</html>
