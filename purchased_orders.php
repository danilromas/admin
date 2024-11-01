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
    WHERE o.status = 'куплен'
";

if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
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
    </style>
</head>
<body>
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
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                <td><?php echo htmlspecialchars($row['date']); ?></td>
                <td><?php echo htmlspecialchars($row['order_name']); ?></td>
                <td><?php echo htmlspecialchars($row['city']); ?></td>
                <td><?php echo htmlspecialchars($row['delivery']); ?></td>
                <td><?php echo htmlspecialchars($row['additional']); ?></td>
                <td><?php echo htmlspecialchars($row['additional_price']); ?></td>
                <td><?php echo htmlspecialchars($row['total_price']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td><?php echo htmlspecialchars($row['computer_name']); ?></td>
                <td><?php echo htmlspecialchars($row['shop_name']); ?></td>
                <td>
    <ul style="list-style-type: none; padding-left: 0; margin: 0;">
        <li><strong>Motherboard:</strong> <?php echo htmlspecialchars($row['motherboard_name']); ?></li>
        <li><strong>Processor:</strong> <?php echo htmlspecialchars($row['processor_name']); ?></li>
        <li><strong>RAM:</strong> <?php echo htmlspecialchars($row['ram_name']); ?></li>
        <li><strong>GPU:</strong> <?php echo htmlspecialchars($row['gpu_name']); ?></li>
        <li><strong>PSU:</strong> <?php echo htmlspecialchars($row['psu_name']); ?></li>
        <li><strong>SSD:</strong> <?php echo htmlspecialchars($row['ssd_name']); ?></li>
        <li><strong>HDD:</strong> <?php echo htmlspecialchars($row['hdd_name']); ?></li>
        <li><strong>Case:</strong> <?php echo htmlspecialchars($row['case_name']); ?></li>
        <li><strong>CPU Cooler:</strong> <?php echo htmlspecialchars($row['cpu_cooler_name']); ?></li>
        <li><strong>Extra Cooler:</strong> <?php echo htmlspecialchars($row['extra_cooler_name']); ?></li>
    </ul>
        </td>
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
