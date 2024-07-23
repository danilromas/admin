<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "computer_sales";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_status'])) {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];

        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $order_id);

        if ($stmt->execute()) {
            echo "Order status updated successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$sql = "SELECT * FROM orders";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders</title>
</head>
<body>
    <h2>Orders</h2>
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
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['date']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['city']); ?></td>
                <td><?php echo htmlspecialchars($row['delivery']); ?></td>
                <td><?php echo htmlspecialchars($row['additional']); ?></td>
                <td><?php echo htmlspecialchars($row['additional_price']); ?></td>
                <td><?php echo htmlspecialchars($row['total_price']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <form action="orders.php" method="POST" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                        <select name="status">
                            <option value="принят" <?php echo $row['status'] == 'принят' ? 'selected' : ''; ?>>принят</option>
                            <option value="собран" <?php echo $row['status'] == 'собран' ? 'selected' : ''; ?>>собран</option>
                            <option value="отправлен" <?php echo $row['status'] == 'отправлен' ? 'selected' : ''; ?>>отправлен</option>
                            <option value="куплен" <?php echo $row['status'] == 'куплен' ? 'selected' : ''; ?>>куплен</option>
                            <option value="отказ" <?php echo $row['status'] == 'отказ' ? 'selected' : ''; ?>>отказ</option>
                        </select>
                        <input type="submit" name="update_status" value="Update Status">
                    </form>
                    <form action="edit_order.php" method="GET" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <input type="submit" value="Edit">
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
