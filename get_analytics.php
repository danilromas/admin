<?php
require 'config.php';

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Получаем даты из GET-параметров
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '1970-01-01';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Получение данных о заказах
// Запрос на данные о заказах и наценке на компьютеры с учётом магазинов
$sql_orders = "
    SELECT
        comp.shop AS store,
        comp.name AS computer_name,
        COUNT(o.id) AS total_sold,
        SUM(o.total_price) AS total_revenue,
        SUM(comp.markup) AS total_markup
    FROM orders o
    JOIN computers comp ON o.computer_id = comp.id
    WHERE o.status = 'куплен' AND o.date BETWEEN ? AND ?
    GROUP BY comp.shop, comp.name";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param("ss", $start_date, $end_date);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();

$total_sales = 0;
$total_revenue = 0;
$total_markup = 0;

while ($row = $result_orders->fetch_assoc()) {
    $total_sales += $row['total_sold'];
    $total_revenue += $row['total_revenue'];
    $total_markup += $row['total_markup'];
}

// Получение данных о расходах
$sql_expenses = "
    SELECT
        store_id,
        SUM(amount) AS total_expense
    FROM expenses
    WHERE date BETWEEN ? AND ?
    GROUP BY store_id";
$stmt_expenses = $conn->prepare($sql_expenses);
$stmt_expenses->bind_param("ss", $start_date, $end_date);
$stmt_expenses->execute();
$result_expenses = $stmt_expenses->get_result();

$total_expenses = 0;
while ($row = $result_expenses->fetch_assoc()) {
    $total_expenses += $row['total_expense'];
}

// Формируем ответ в формате JSON
$response = [
    'totalSales' => $total_sales,
    'totalRevenue' => $total_revenue,
    'totalMarkup' => $total_markup,
    'totalExpenses' => $total_expenses
];

echo json_encode($response);

$conn->close();
?>
