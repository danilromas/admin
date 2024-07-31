<?php
// Подключение к базе данных
require 'config.php';  // Подключение файла конфигурации


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Установка кодировки
if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
    exit();
}

// Получение дат из GET-параметров
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '1970-01-01';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Запрос на данные о заказах
$sql_orders = "
    SELECT
        o.name AS computer_name,
        COUNT(o.id) AS total_sold,
        SUM(o.total_price) AS total_revenue
    FROM orders o
    WHERE o.status = 'куплен' AND o.date BETWEEN ? AND ?
    GROUP BY o.name";
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

// Получение общего количества проданных товаров и выручки
$total_sold = 0;
$total_revenue = 0;
$labels = [];
$sold_data = [];  // Данные для графика количества проданных товаров
$revenue_data = [];  // Данные для графика выручки
while ($row = $result_orders->fetch_assoc()) {
    $labels[] = '"' . htmlspecialchars($row['computer_name']) . '"';
    $sold_data[] = $row['total_sold'];
    $revenue_data[] = $row['total_revenue'];
    $total_sold += $row['total_sold'];
    $total_revenue += $row['total_revenue'];
}

// Запрос на данные о компонентах
$sql_components = "
    SELECT
        c.name AS component_name,
        c.price AS component_price,
        IFNULL(SUM(o.total_price - o.additional_price), 0) AS total_cost,
        (c.price * c.quantity) AS total_value
    FROM components c
    LEFT JOIN orders o ON o.computer_id = c.id AND o.status = 'куплен' AND o.date BETWEEN ? AND ?
    GROUP BY c.name, c.price, c.quantity";
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

// Расчет расходов и прибыли
$total_expenses = 0;
$total_markup = 0;

$components_data = [];
while ($row = $result_components->fetch_assoc()) {
    $component_name = htmlspecialchars($row['component_name']);
    $component_cost = $row['total_cost'];
    $component_value = $row['total_value'];
    $markup = $component_value - $component_cost;

    $total_expenses += $component_cost;
    $total_markup += $markup;

    $components_data[] = [
        'name' => $component_name,
        'cost' => number_format($component_cost, 2),
        'value' => number_format($component_value, 2),
        'markup' => number_format($markup, 2)
    ];
}

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
            width: 80%;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
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
        }
        .form-group input {
            padding: 5px;
            width: 100%;
            max-width: 200px;
        }
        .charts {
            margin-top: 40px;
        }
        canvas {
            width: 100%;
            height: 400px;
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
        <p>Total Revenue: $<?php echo htmlspecialchars(number_format($total_revenue, 2)); ?></p>

        <h2>Sold Computers</h2>
        <table>
            <thead>
                <tr>
                    <th>Computer Name</th>
                    <th>Total Sold</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result_orders->data_seek(0);
                while ($row = $result_orders->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['computer_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['total_sold']) . "</td>";
                    echo "<td>$" . htmlspecialchars(number_format($row['total_revenue'], 2)) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Components in Stock</h2>
        <table>
            <thead>
                <tr>
                    <th>Component Name</th>
                    <th>Cost</th>
                    <th>Value</th>
                    <th>Markup</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($components_data as $data) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($data['name']) . "</td>";
                    echo "<td>$" . htmlspecialchars($data['cost']) . "</td>";
                    echo "<td>$" . htmlspecialchars($data['value']) . "</td>";
                    echo "<td>$" . htmlspecialchars($data['markup']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Expenses and Profit</h2>
        <p>Total Expenses: $<?php echo htmlspecialchars(number_format($total_expenses, 2)); ?></p>
        <p>Total Markup (Profit): $<?php echo htmlspecialchars(number_format($total_markup, 2)); ?></p>

        <div class="charts">
            <h2>Sales Charts</h2>
            <canvas id="salesChart"></canvas>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var ctxSales = document.getElementById('salesChart').getContext('2d');
                var salesChart = new Chart(ctxSales, {
                    type: 'bar',
                    data: {
                        labels: [<?php echo implode(',', $labels); ?>],
                        datasets: [{
                            label: 'Number of Units Sold',
                            data: [<?php echo implode(',', $sold_data); ?>],
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        </script>
    </div>
</body>
</html>
