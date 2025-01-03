<?php
// Подключение к базе данных
require 'config.php';

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4"); // Установка кодировки

// Получение дат из GET-параметров
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '1970-01-01';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

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
if ($stmt_orders === false) {
    die("Error preparing orders statement: " . $conn->error);
}
$stmt_orders->bind_param("ss", $start_date, $end_date);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
if ($result_orders === false) {
    die("Error executing orders statement: " . $stmt_orders->error);
}

// Инициализация данных для отчёта
$total_sold = 0;
$total_revenue = 0;
$total_markup = 0;
$stores_data = []; // Данные по магазинам

while ($row = $result_orders->fetch_assoc()) {
    $store = htmlspecialchars($row['store']);
    $computer_name = htmlspecialchars($row['computer_name']);
    
    $stores_data[$store]['sold'][$computer_name] = $row['total_sold'];
    $stores_data[$store]['revenue'][$computer_name] = $row['total_revenue'];
    $stores_data[$store]['markup'][$computer_name] = $row['total_markup'];
    
    $total_sold += $row['total_sold'];
    $total_revenue += $row['total_revenue'];
    $total_markup += $row['total_markup'];
}

// Получение данных о компонентах и их затратах
$sql_components = "
    SELECT
        c.name AS component_name,
        SUM(ca.quantity) AS total_quantity,
        SUM(ca.quantity * ca.price) AS total_cost
    FROM components c
    JOIN component_arrivals ca ON c.id = ca.component_id
    WHERE ca.arrival_date BETWEEN ? AND ?
    GROUP BY c.name";
$stmt_components = $conn->prepare($sql_components);
if ($stmt_components === false) {
    die("Error preparing components statement: " . $conn->error);
}
$stmt_components->bind_param("ss", $start_date, $end_date);
$stmt_components->execute();
$result_components = $stmt_components->get_result();
if ($result_components === false) {
    die("Error executing components statement: " . $stmt_components->error);
}

$components_data = [];
$total_quantity = 0;
$total_cost = 0;

while ($row = $result_components->fetch_assoc()) {
    $component_name = htmlspecialchars($row['component_name']);
    $total_quantity += $row['total_quantity'];
    $total_cost += floatval($row['total_cost']); // Преобразование в float и суммирование

    $components_data[] = [
        'name' => $component_name,
        'quantity' => $row['total_quantity'],
        'cost' => number_format($row['total_cost'], 2)
    ];
}

// Запрос на получение расходов по магазинам
$sql_expenses = "
    SELECT
        store_id,
        category,
        SUM(amount) AS total_expense
    FROM expenses
    WHERE date BETWEEN ? AND ?
    GROUP BY store_id, category";
$stmt_expenses = $conn->prepare($sql_expenses);
if ($stmt_expenses === false) {
    die("Error preparing expenses statement: " . $conn->error);
}
$stmt_expenses->bind_param("ss", $start_date, $end_date);
$stmt_expenses->execute();
$result_expenses = $stmt_expenses->get_result();
if ($result_expenses === false) {
    die("Error executing expenses statement: " . $stmt_expenses->error);
}

$expenses_data = [];
$total_expense = 0;
$store_totals = [];

// Обработка данных о расходах
while ($row = $result_expenses->fetch_assoc()) {
    $store_id = $row['store_id'];
    $category = htmlspecialchars($row['category']);
    $expense = floatval($row['total_expense']);

    $expenses_data[$store_id][$category] = $expense;
    $store_totals[$store_id] = ($store_totals[$store_id] ?? 0) + $expense;
    $total_expense += $expense;
}

$store_names = [
    1 => "Techpower",
    2 => "HQ",
    3 => "Artem",
    4 => "Another Store"
];

// Закрытие соединения
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.container {
    width: 90%; /* Увеличиваем ширину контейнера для мобильных */
    max-width: 800px; /* Ограничиваем максимальную ширину */
    margin: 50px auto;
    background: #fff;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
    color: #333;
    font-size: 24px; /* Уменьшаем размер заголовка для мобильных */
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table, th, td {
    border: 1px solid #ddd;
}

th, td {
    padding: 12px;
    text-align: center;
    font-size: 14px; /* Уменьшаем размер шрифта для мобильных */
}

th {
    background-color: #f4f4f4;
    color: #333;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #f1f1f1;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 14px; /* Уменьшаем размер шрифта для мобильных */
}

.form-group input {
    padding: 5px;
    width: 100%;
    max-width: 100%; /* Увеличиваем ширину поля ввода на мобильных */
}

.charts {
    margin-top: 40px;
}

canvas {
    width: 100%;
    height: 300px; /* Уменьшаем высоту графика на мобильных */
}

/* Медиазапрос для мобильных устройств */
@media (max-width: 600px) {
    h1 {
        font-size: 20px; /* Уменьшаем размер заголовка на мобильных */
    }

    th, td {
        font-size: 12px; /* Уменьшаем размер шрифта в таблице на мобильных */
        padding: 8px; /* Уменьшаем отступы в ячейках на мобильных */
    }

    .form-group input {
        max-width: 100%; /* Поля ввода занимают всю ширину на мобильных */
    }

    canvas {
        height: 250px; /* Уменьшаем высоту графиков на мобильных */
    }
}

    </style>
</head>
<body>
    <div class="container">
        <h1>Sales Report</h1>

        <form action="analytics.php" method="get">
            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            <div class="form-group">
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
            <button type="submit">Generate Report</button>
        </form>

        <h2>Order Information</h2>
        <p>Total Orders: <?php echo htmlspecialchars($total_sold); ?></p>
        <p>Total Revenue: <?php echo htmlspecialchars(number_format($total_revenue, 2)); ?> руб</p>
        <p>Total Markup (Profit): <?php echo htmlspecialchars(number_format($total_markup, 2)); ?> руб</p>

        <h2>Sold Computers by Store</h2>
        <table>
            <thead>
                <tr>
                    <th>Store</th>
                    <th>Computer Name</th>
                    <th>Total Sold</th>
                    <th>Total Revenue</th>
                    <th>Total Markup</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($stores_data as $store => $data) {
                    foreach ($data['sold'] as $computer_name => $total_sold) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($store) . "</td>";
                        echo "<td>" . htmlspecialchars($computer_name) . "</td>";
                        echo "<td>" . htmlspecialchars($total_sold) . "</td>";
                        echo "<td>" . htmlspecialchars(number_format($data['revenue'][$computer_name], 2)) . " руб</td>";
                        echo "<td>" . htmlspecialchars(number_format($data['markup'][$computer_name], 2)) . " руб</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>


        <h2>Store Expenses</h2>
        <table>
            <thead>
                <tr>
                    <th>Store Name</th>
                    <th>Category</th>
                    <th>Total Expense</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($expenses_data as $store_id => $categories) {
                    // Определяем имя магазина на основе ID
                    $store_name = isset($store_names[$store_id]) ? $store_names[$store_id] : 'Unknown Store';
                    
                    // Распечатываем расходы по категориям для текущего магазина
                    foreach ($categories as $category => $expense) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($store_name) . "</td>";
                        echo "<td>" . htmlspecialchars($category) . "</td>";
                        echo "<td>" . htmlspecialchars(number_format($expense, 2)) . " руб</td>";
                        echo "</tr>";
                    }

                    // Распечатываем общую сумму расходов для текущего магазина
                    echo "<tr>";
                    echo "<td><strong>" . htmlspecialchars($store_name) . " Total</strong></td>";
                    echo "<td><strong>Total</strong></td>";
                    echo "<td><strong>" . htmlspecialchars(number_format($store_totals[$store_id], 2)) . "руб</strong></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>Grand Total</strong></td>
                    <td><strong><?php echo number_format($total_expense, 2); ?> руб</strong></td>
                </tr>
            </tfoot>
        </table>

        

        <div class="charts">
            <canvas id="profitChart"></canvas>
        </div>

        <script>
            var ctx = document.getElementById('profitChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_keys($stores_data)); ?>,
                    datasets: [
                        {
                            label: 'Total Revenue',
                            backgroundColor: 'rgb(75, 192, 192)',
                            borderColor: 'rgb(75, 192, 192)',
                            data: <?php echo json_encode(array_map(function($store) {
                                return array_sum($store['revenue']);
                            }, $stores_data)); ?>
                        },
                        {
                            label: 'Total Markup',
                            backgroundColor: 'rgb(153, 102, 255)',
                            borderColor: 'rgb(153, 102, 255)',
                            data: <?php echo json_encode(array_map(function($store) {
                                return array_sum($store['markup']);
                            }, $stores_data)); ?>
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Stores'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Amount ($)'
                            }
                        }
                    }
                }
            });
        </script>
        <h2>Components Expenses</h2>
        <table>
            <thead>
                <tr>
                    <th>Component Name</th>
                    <th>Total Quantity</th>
                    <th>Total Cost</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($components_data as $component) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($component['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($component['quantity']) . "</td>";
                    echo "<td>" . htmlspecialchars($component['cost']) . " руб</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong><?php echo htmlspecialchars($total_quantity); ?></strong></td>
                    <td><strong><?php echo htmlspecialchars(number_format($total_cost, 2)); ?>руб.</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
</body>
</html>
