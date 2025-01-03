<?php
require 'config.php';  // Подключение файла конфигурации

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Функция для отправки сообщения в Telegram
function sendTelegramMessage($message) {
    $token = '6811663386:AAFD--8cBLJjjac0maWmW_-7GcmXzV1B3to'; // Укажите ваш токен
    $chat_id = '-1002060916773'; // Укажите ваш chat_id
    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($message);
    
    // Выполнение HTTP-запроса
    file_get_contents($url);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_status'])) {
        $order_id = intval($_POST['order_id']);
        
        // Получаем имя заказчика по order_id
        $order_name_query = "SELECT name FROM orders WHERE id = ?";
        $stmt = $conn->prepare($order_name_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->bind_result($order_name);
        $stmt->fetch();
        $stmt->close();

        // Отладка: проверьте, что статус приходит из формы
        $status = isset($_POST['status']) ? $_POST['status'] : null;
        if ($status === null) {
            echo "<p>Статус не был передан из формы.</p>";
            return; // Завершаем выполнение, если статус не передан
        }

        // Экранирование входящих данных
        $status = $conn->real_escape_string($status);
        $additional_price = floatval($_POST['additional_price']); // Получаем значение из формы

        // Получение текущей итоговой цены
        $base_price_query = "SELECT total_price FROM orders WHERE id = ?";
        $stmt = $conn->prepare($base_price_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->bind_result($current_total_price);
        $stmt->fetch();
        $stmt->close();

        // Обновление статуса и final_price
        // Если вы хотите сохранить текущее значение total_price без изменений
        $final_price = $current_total_price; // Используем текущее значение без изменений

        $sql = "UPDATE orders SET status = ?, total_price = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdi", $status, $final_price, $order_id);

        if ($stmt->execute()) {
            echo "<p>Статус заказа обновлён успешно.</p>";
        
            // Формирование сообщения с именем заказа
            $message = "Статус заказа: Имя заказчика: '$order_name' изменён на '$status' для заказа ID: $order_id.";
            sendTelegramMessage($message);
        
            // Если статус изменился на "куплен", обновляем количество компонентов
            if ($status === 'куплен') {
                // Получение всех компонентов из заказа
                $sqlGetComponents = "
                    SELECT 
                        motherboard_id, processor_id, ram_id, gpu_id, psu_id, 
                        ssd_id, hdd_id, case_id, cpu_cooler_id, extra_cooler_id 
                    FROM orders 
                    WHERE id = ?
                ";
                $stmtGetComponents = $conn->prepare($sqlGetComponents);
                $stmtGetComponents->bind_param("i", $order_id);
                $stmtGetComponents->execute();
                $stmtGetComponents->bind_result(
                    $motherboard_id, $processor_id, $ram_id, $gpu_id, $psu_id, 
                    $ssd_id, $hdd_id, $case_id, $cpu_cooler_id, $extra_cooler_id
                );
                $stmtGetComponents->fetch();
                $stmtGetComponents->close();
        
                // Список компонентов для обновления
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
        
                // Обновление количества компонентов
                foreach ($componentIds as $componentId) {
                    if ($componentId) {
                        $updateSql = "UPDATE components SET quantity = quantity - 1 WHERE id = ?";
                        $updateStmt = $conn->prepare($updateSql);
                        $updateStmt->bind_param("i", $componentId);
                        if (!$updateStmt->execute()) {
                            echo "Ошибка обновления количества компонента с ID: " . $componentId;
                        }
                        $updateStmt->close();
                    }
                }
            }
        } else {
            echo "<p>Ошибка обновления: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } elseif (isset($_POST['delete_order'])) {
        $order_id = intval($_POST['order_id']);

        $sql = "DELETE FROM orders WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);

        if ($stmt->execute()) {
            echo "<p>Заказ успешно удалён.</p>";
        } else {
            echo "<p>Ошибка: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}



// Get sorting parameters
$order_by = isset($_GET['order_by']) ? $conn->real_escape_string($_GET['order_by']) : 'date';
$sort = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : 'ASC';

// Validate sorting parameters
$valid_columns = ['date', 'order_name', 'city', 'delivery', 'additional', 'additional_price', 'total_price', 'status', 'computer_name', 'shop_name'];
if (!in_array($order_by, $valid_columns)) {
    $order_by = 'date';
}

if ($sort !== 'ASC' && $sort !== 'DESC') {
    $sort = 'ASC';
}

// Join orders with computers
$sql = "
    SELECT 
        o.id AS order_id,
        o.date,
        o.name AS order_name,
        o.city,
        o.delivery,
        o.additional,
        o.additional_price,
        o.total_price AS total_price,
        o.status,
        c.name AS computer_name,
        c.shop AS shop_name,
        m.name AS motherboard_name,
        p.name AS processor_name,
        r.name AS ram_name,
        g.name AS gpu_name,
        ps.name AS psu_name,
        ssd.name AS ssd_name,
        hdd.name AS hdd_name,
        cs.name AS case_name,
        cpu_c.name AS cpu_cooler_name,
        e_c.name AS extra_cooler_name
    FROM orders o
    JOIN computers c ON o.computer_id = c.id
    LEFT JOIN components m ON o.motherboard_id = m.id
    LEFT JOIN components p ON o.processor_id = p.id
    LEFT JOIN components r ON o.ram_id = r.id
    LEFT JOIN components g ON o.gpu_id = g.id
    LEFT JOIN components ps ON o.psu_id = ps.id
    LEFT JOIN components ssd ON o.ssd_id = ssd.id
    LEFT JOIN components hdd ON o.hdd_id = hdd.id
    LEFT JOIN components cs ON o.case_id = cs.id
    LEFT JOIN components cpu_c ON o.cpu_cooler_id = cpu_c.id
    LEFT JOIN components e_c ON o.extra_cooler_id = e_c.id
    WHERE o.status IN ('куплен', 'отказ')
    ORDER BY $order_by $sort
";


$result = $conn->query($sql);

// Check for SQL errors
if (!$result) {
    die("Error executing query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 20px;
    background-color: #f4f4f4;
    color: #333;
}

h2 {
    font-size: 28px;
    color: #4A4A4A;
    margin-bottom: 20px;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 20px;
}

th, td {
    padding: 12px 15px;
    text-align: left;
}

th {
    background-color: #007BFF;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    font-size: 14px;
    border-bottom: 3px solid #0056b3;
}

th a {
    color: #fff;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

th a i {
    margin-left: 5px;
}

td {
    background-color: #f9f9f9;
    border-bottom: 1px solid #dddddd;
    font-size: 14px;
    color: #4A4A4A;
}

td:last-child {
    text-align: center;
}

tr:hover td {
    background-color: #f1f1f1;
}

button {
    padding: 6px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    color: #fff;
    font-size: 14px;
    margin: 2px;
}

.update-status {
    background-color: #28A745;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.delete-order {
    background-color: #DC3545;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.edit-order {
    background-color: #007BFF;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

select {
    padding: 5px;
    font-size: 14px;
    border-radius: 5px;
    border: 1px solid #cccccc;
    background-color: #ffffff;
}

td form {
    display: inline-block;
}

input[type="submit"] {
    cursor: pointer;
    padding: 8px 12px;
    border-radius: 5px;
    border: none;
    font-size: 14px;
    font-weight: 600;
}

input[type="submit"]:hover {
    opacity: 0.8;
}

.back-button {
    display: inline-block;
    margin: 20px;
    padding: 10px 20px;
    color: #007BFF;
    text-decoration: none;
    background-color: #f4f4f4;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-weight: bold;
    transition: background-color 0.2s;
}

.back-button:hover {
    background-color: #e0e0e0;
}

/* Медиазапрос для мобильной версии */
@media (max-width: 768px) {
    body {
        margin: 10px; /* Уменьшаем внешние отступы */
    }

    h2 {
        font-size: 24px; /* Уменьшаем размер заголовка */
    }

    table {
        margin: 0; /* Убираем нижний отступ у таблицы */
    }

    th, td {
        padding: 8px; /* Уменьшаем отступы ячеек */
        font-size: 12px; /* Уменьшаем размер шрифта */
    }

    button {
        font-size: 12px; /* Уменьшаем размер шрифта кнопок */
        padding: 4px 8px; /* Уменьшаем отступы кнопок */
    }

    .back-button {
        margin: 10px; /* Уменьшаем отступы у кнопки "Назад" */
        padding: 8px 12px; /* Уменьшаем отступы у кнопки "Назад" */
        font-size: 12px; /* Уменьшаем размер шрифта кнопки "Назад" */
    }
}

    </style>
</head>
<body>
<h2>Orders</h2>
    <a href="index.php" class="back-button">Назад</a>
    <table>
        <tr>
            <th><a href="?order_by=id&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">ID <i class="fas fa-sort<?php echo $order_by === 'id' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=date&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Дата <i class="fas fa-sort<?php echo $order_by === 'date' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=order_name&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Имя<i class="fas fa-sort<?php echo $order_by === 'order_name' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=city&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Город <i class="fas fa-sort<?php echo $order_by === 'city' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=delivery&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Доставка <i class="fas fa-sort<?php echo $order_by === 'delivery' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=additional&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Дополнения <i class="fas fa-sort<?php echo $order_by === 'additional' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=additional_price&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Доп Цена<i class="fas fa-sort<?php echo $order_by === 'additional_price' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=total_price&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Финальная цена<i class="fas fa-sort<?php echo $order_by === 'total_price' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th> <!-- Изменено на Final Price -->
            <th><a href="?order_by=status&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Статус<i class="fas fa-sort<?php echo $order_by === 'status' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=computer_name&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">ПК<i class="fas fa-sort<?php echo $order_by === 'computer_name' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=shop_name&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Магазин <i class="fas fa-sort<?php echo $order_by === 'shop_name' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th>Характеристики</th>
            <th>Статус</th>
            <th>Кнопка</th>
        </tr>
                <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['order_id'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['date'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['order_name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['city'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['delivery'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['additional'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['additional_price'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['total_price'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['status'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['computer_name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['shop_name'] ?? ''); ?></td>
                
                
                <td>
                    <ul style="list-style-type: none; padding-left: 0; margin: 0;">
                        <li><strong>Motherboard:</strong> <?php echo htmlspecialchars($row['motherboard_name'] ?? ''); ?></li>
                        <li><strong>Processor:</strong> <?php echo htmlspecialchars($row['processor_name'] ?? ''); ?></li>
                        <li><strong>RAM:</strong> <?php echo htmlspecialchars($row['ram_name'] ?? ''); ?></li>
                        <li><strong>GPU:</strong> <?php echo htmlspecialchars($row['gpu_name'] ?? ''); ?></li>
                        <li><strong>PSU:</strong> <?php echo htmlspecialchars($row['psu_name'] ?? ''); ?></li>
                        <li><strong>SSD:</strong> <?php echo htmlspecialchars($row['ssd_name'] ?? ''); ?></li>
                        <li><strong>HDD:</strong> <?php echo htmlspecialchars($row['hdd_name'] ?? ''); ?></li>
                        <li><strong>Case:</strong> <?php echo htmlspecialchars($row['case_name'] ?? ''); ?></li>
                        <li><strong>CPU Cooler:</strong> <?php echo htmlspecialchars($row['cpu_cooler_name'] ?? ''); ?></li>
                        <li><strong>Extra Cooler:</strong> <?php echo htmlspecialchars($row['extra_cooler_name'] ?? ''); ?></li>
                    </ul>
                </td>
                <td>
                    <form action="orders.php" method="POST" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id'] ?? ''); ?>">
                        <select name="status" class="status-select">
                        <option value="Создан(на согласовании)" <?php echo ($row['status'] ?? '') == 'Создан(на согласовании)' ? 'selected' : ''; ?>>Создан(на согласовании)</option>
                        <option value="Согласован" <?php echo ($row['status'] ?? '') == 'Согласован' ? 'selected' : ''; ?>>Согласован</option>
                        <option value="У СБ" <?php echo ($row['status'] ?? '') == 'У СБ' ? 'selected' : ''; ?>>У СБ</option>
                        <option value="В пути" <?php echo ($row['status'] ?? '') == 'В пути' ? 'selected' : ''; ?>>В пути</option>
                        <option value="куплен" <?php echo ($row['status'] ?? '') == 'куплен' ? 'selected' : ''; ?>>куплен</option>
                        <option value="отказ" <?php echo ($row['status'] ?? '') == 'отказ' ? 'selected' : ''; ?>>отказ</option>
                        </select>
                        <input type="hidden" name="additional_price" value="<?php echo htmlspecialchars($row['additional_price'] ?? ''); ?>"> <!-- Добавляем дополнительную цену -->
                        <input type="submit" name="update_status" class="update-status" value="Update Status">
                    </form>
                    <form action="orders.php" method="POST" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id'] ?? ''); ?>">
                        <input type="submit" name="delete_order" class="delete-order" value="Delete" onclick="return confirm('Are you sure you want to delete this order?');">
                    </form>
                    <form action="edit_order.php" method="GET" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['order_id'] ?? ''); ?>">
                        <input type="submit" class="edit-order" value="Edit">
                    </form>
                    <td>
                        <a href="security.php"><button class="edit-order">Добавить информацию по службе безопасности</button></a>
                    </td>
                    
                    
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>

<?php
$conn->close();
?>
