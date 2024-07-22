<?php
// Включить отображение всех ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "computer_sales";

// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);

// Установка кодировки
if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
    exit();
}

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Обработка удаления
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM components WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $delete_id);
    if ($delete_stmt->execute()) {
        echo "Component deleted successfully.";
    } else {
        echo "Error deleting component: " . $delete_stmt->error;
    }
    $delete_stmt->close();
}

// Получение данных из таблицы components
$sql = "SELECT * FROM components";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Components List</title>
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
        .btn {
            padding: 5px 10px;
            text-decoration: none;
            color: #fff;
            border-radius: 3px;
        }
        .btn-edit {
            background-color: #4CAF50;
        }
        .btn-delete {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Components List</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>$" . htmlspecialchars(number_format($row['price'], 2)) . "</td>";
                        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                        echo "<td>
                            <a class='btn btn-edit' href='edit_component.php?id=" . htmlspecialchars($row['id']) . "'>Edit</a>
                            <a class='btn btn-delete' href='components.php?delete_id=" . htmlspecialchars($row['id']) . "' onclick='return confirm(\"Are you sure you want to delete this component?\")'>Delete</a>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No components found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>