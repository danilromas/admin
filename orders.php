<?php
require 'config.php';  // Подключение файла конфигурации

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_status'])) {
        $order_id = intval($_POST['order_id']);
        $status = $conn->real_escape_string($_POST['status']);

        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $order_id);

        if ($stmt->execute()) {
            echo "<p>Order status updated successfully.</p>";
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } elseif (isset($_POST['delete_order'])) {
        $order_id = intval($_POST['order_id']);

        $sql = "DELETE FROM orders WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);

        if ($stmt->execute()) {
            echo "<p>Order deleted successfully.</p>";
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
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
        o.total_price,
        o.status,
        c.name AS computer_name,
        c.shop AS shop_name
    FROM orders o
    JOIN computers c ON o.computer_id = c.id
    WHERE o.status NOT IN ('куплен', 'отказ')
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
            position: relative;
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
        th a.sort-asc i {
            transform: rotate(180deg);
        }
        td {
            background-color: #f9f9f9;
        }
        td form {
            display: inline;
        }
        button {
            padding: 6px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            color: #fff;
            font-size: 14px;
            margin: 2px;
        }
        .update-status {
            background-color: #28A745;
        }
        .delete-order {
            background-color: #DC3545;
        }
        .edit-order {
            background-color: #007BFF;
        }
        select {
            padding: 5px;
        }
    </style>
</head>
<body>
<body>
    <h2>Orders</h2>
    <table>
        <tr>
            <th><a href="?order_by=id&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">ID <i class="fas fa-sort<?php echo $order_by === 'id' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=date&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Date <i class="fas fa-sort<?php echo $order_by === 'date' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=order_name&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Name <i class="fas fa-sort<?php echo $order_by === 'order_name' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=city&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">City <i class="fas fa-sort<?php echo $order_by === 'city' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=delivery&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Delivery <i class="fas fa-sort<?php echo $order_by === 'delivery' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=additional&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Additional <i class="fas fa-sort<?php echo $order_by === 'additional' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=additional_price&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Additional Price <i class="fas fa-sort<?php echo $order_by === 'additional_price' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=total_price&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Total Price <i class="fas fa-sort<?php echo $order_by === 'total_price' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=status&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Status <i class="fas fa-sort<?php echo $order_by === 'status' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=computer_name&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Computer <i class="fas fa-sort<?php echo $order_by === 'computer_name' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th><a href="?order_by=shop_name&sort=<?php echo $sort === 'ASC' ? 'DESC' : 'ASC'; ?>">Shop <i class="fas fa-sort<?php echo $order_by === 'shop_name' ? ($sort === 'ASC' ? '' : '-desc') : ''; ?>"></i></a></th>
            <th>Actions</th>
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
                    <form action="orders.php" method="POST" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                        <select name="status" class="status-select">
                            <option value="принят" <?php echo $row['status'] == 'принят' ? 'selected' : ''; ?>>принят</option>
                            <option value="собран" <?php echo $row['status'] == 'собран' ? 'selected' : ''; ?>>собран</option>
                            <option value="отправлен" <?php echo $row['status'] == 'отправлен' ? 'selected' : ''; ?>>отправлен</option>
                            <option value="куплен" <?php echo $row['status'] == 'куплен' ? 'selected' : ''; ?>>куплен</option>
                            <option value="отказ" <?php echo $row['status'] == 'отказ' ? 'selected' : ''; ?>>отказ</option>
                        </select>
                        <input type="submit" name="update_status" class="update-status" value="Update Status">
                    </form>
                    <form action="orders.php" method="POST" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                        <input type="submit" name="delete_order" class="delete-order" value="Delete" onclick="return confirm('Are you sure you want to delete this order?');">
                    </form>
                    <form action="edit_order.php" method="GET" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                        <input type="submit" class="edit-order" value="Edit">
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>

<?php
$conn->close();
?>
