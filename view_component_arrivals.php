<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "computer_sales";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Обработка POST-запроса для обновления статуса
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['deliver_id'])) {
        $deliver_id = intval($_POST['deliver_id']);

        // Обновление статуса поступления на "доставлен"
        $stmt_update = $conn->prepare("
            UPDATE component_arrivals
            SET status = 'delivered'
            WHERE id = ?
        ");
        $stmt_update->bind_param("i", $deliver_id);
        $stmt_update->execute();
        $stmt_update->close();

        // Обновление количества компонента на складе
        $stmt_component = $conn->prepare("
            UPDATE components
            SET quantity = quantity + (
                SELECT quantity
                FROM component_arrivals
                WHERE id = ?
            )
            WHERE id = (
                SELECT component_id
                FROM component_arrivals
                WHERE id = ?
            )
        ");
        $stmt_component->bind_param("ii", $deliver_id, $deliver_id);
        $stmt_component->execute();
        $stmt_component->close();

        // Перенаправление на текущую страницу после обновления
        header("Location: view_component_arrivals.php");
        exit();
    }

    if (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);

        // Удаление записи о поступлении
        $stmt_delete = $conn->prepare("
            DELETE FROM component_arrivals
            WHERE id = ?
        ");
        $stmt_delete->bind_param("i", $delete_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        // Перенаправление на текущую страницу после удаления
        header("Location: view_component_arrivals.php");
        exit();
    }
}

// Получение данных о поступлениях компонентов
$sql_arrivals = "
    SELECT ca.id, c.name AS component_name, ca.quantity, ca.price, ca.arrival_date, ca.status
    FROM component_arrivals ca
    JOIN components c ON ca.component_id = c.id
    ORDER BY ca.arrival_date DESC
";
$result_arrivals = $conn->query($sql_arrivals);

// Закрытие соединения
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Component Arrivals</title>
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
        .table-container {
            width: 80%;
            max-width: 1000px;
            margin: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        .btn-deliver, .btn-delete {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: #fff;
        }
        .btn-deliver {
            background-color: #28a745;
        }
        .btn-deliver:hover {
            background-color: #218838;
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="table-container">
        <h2>Component Arrivals</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Component</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Arrival Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
            if ($result_arrivals && $result_arrivals->num_rows > 0) {
                while ($row = $result_arrivals->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['component_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['price']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['arrival_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>";
                    if ($row['status'] == 'in_transit') {
                        echo "<form action='view_component_arrivals.php' method='post' style='display:inline;'>
                            <input type='hidden' name='deliver_id' value='" . htmlspecialchars($row['id']) . "'>
                            <button type='submit' class='btn-deliver'>Deliver</button>
                        </form>";
                    } else {
                        echo "<span>--</span>";
                    }
                    echo "<form action='view_component_arrivals.php' method='post' style='display:inline;'>
                        <input type='hidden' name='delete_id' value='" . htmlspecialchars($row['id']) . "'>
                        <button type='submit' class='btn-delete'>Delete</button>
                    </form>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No component arrivals found.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>