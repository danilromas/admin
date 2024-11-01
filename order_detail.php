<?php
// Подключение к базе данных
include 'db_connection.php';

// Получаем ID заказа
$order_id = intval($_GET['id']);

// Получаем информацию о заказе
$sql = "
    SELECT 
        o.*,
        c.* -- Выберите нужные поля
    FROM orders o
    LEFT JOIN clients c ON o.id = c.order_id
    WHERE o.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Детали заказа #<?php echo $order_id; ?></title>
</head>
<body>
    <h1>Детали заказа #<?php echo $order_id; ?></h1>
    <!-- Здесь выведите информацию о заказе и клиенте -->
    <p>Дата заказа: <?php echo htmlspecialchars($order['date']); ?></p>
    <p>Клиент: <?php echo htmlspecialchars($order['client_avito_name']); ?></p>
    <p>Телефон: <?php echo htmlspecialchars($order['client_phone']); ?></p>
    <p>Ссылка на Авито: <a href="<?php echo htmlspecialchars($order['client_avito_link']); ?>" target="_blank">Перейти</a></p>
    <!-- Добавьте другую необходимую информацию о заказе -->
    <a href="security_view.php">Назад к списку клиентов</a>
</body>
</html>
