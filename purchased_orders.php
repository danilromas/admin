<?php
require 'config.php';  // Подключение файла конфигурации

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
    SELECT 
        o.id AS order_id,
        o.date,
        o.name AS order_name,
        o.city,
        o.delivery,
        o.additional,
        o.additional_price,
        o.total_price,
        o.status,
        c.name AS computer_name,
        c.shop AS shop_name
    FROM orders o
    JOIN computers c ON o.computer_id = c.id
    WHERE o.status = 'куплен'
";

if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        ?>

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Purchased Orders</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    background-color: #f4f4f4;
                }
                h2 {
                    font-size: 24px;
                    color: #333;
                    margin-bottom: 20px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    background-color: #fff;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                    margin-bottom: 20px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 12px;
                    text-align: left;
                }
                th {
                    background-color: #007BFF;
                    color: #fff;
                }
                td {
                    background-color: #f9f9f9;
                }
            </style>
        </head>
        <body>
            <h2>Purchased Orders</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Name</th>
                    <th>City</th>
                    <th>Delivery</th>
                    <th>Additional</th>
                    <th>Additional Price</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Computer</th>
                    <th>Shop</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['order_id']; ?></td>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo htmlspecialchars($row['order_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['city']); ?></td>
                        <td><?php echo htmlspecialchars($row['delivery']); ?></td>
                        <td><?php echo htmlspecialchars($row['additional']); ?></td>
                        <td><?php echo htmlspecialchars($row['additional_price']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_price']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['computer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['shop_name']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </body>
        </html>

        <?php
    } else {
        echo "No purchased orders found.";
    }
    $result->free();
} else {
    echo "Error executing query: " . $conn->error;
}

$conn->close();
?>
