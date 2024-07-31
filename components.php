<?php
// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "computer_sales";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Установка кодировки
if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
    exit();
}

// Обработка удаления
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM components WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    if ($delete_stmt === false) {
        die("Error preparing delete statement: " . $conn->error);
    }
    $delete_stmt->bind_param("i", $delete_id);
    if ($delete_stmt->execute()) {
        echo "Component deleted successfully.";
    } else {
        echo "Error deleting component: " . $delete_stmt->error;
    }
    $delete_stmt->close();
}

// Поиск
$search = '';
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

// Сортировка по категории
$category = '';
if (isset($_GET['category'])) {
    $category = $conn->real_escape_string($_GET['category']);
}

// Построение SQL-запроса
$sql = "SELECT * FROM components WHERE 1=1";

$params = [];
$types = '';

if ($search) {
    $sql .= " AND name LIKE ?";
    $params[] = '%' . $search . '%';
    $types .= 's'; // Строковый тип для параметра поиска
}

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= 's'; // Строковый тип для параметра категории
}

$sql .= " ORDER BY name";

// Подготовка запроса
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Связывание параметров
if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Получение средней цены
// Получение средней цены
$sql_avg_price = "
    SELECT
        c.id,
        c.name,
        c.price AS component_price,
        c.category,
        c.photo,
        c.quantity AS initial_quantity,
        IFNULL(SUM(ca.price * ca.quantity), 0) AS total_arrival_price,
        IFNULL(SUM(ca.quantity), 0) AS total_quantity_arrivals,
        CASE
            WHEN (c.quantity - IFNULL(SUM(ca.quantity), 0)) >= 0
            THEN (
                (c.price * (c.quantity - IFNULL(SUM(ca.quantity), 0))) + IFNULL(SUM(ca.price * ca.quantity), 0)
            ) / c.quantity
            ELSE IFNULL(SUM(ca.price * ca.quantity), 0) / IFNULL(SUM(ca.quantity), 0)
        END AS average_price
    FROM components c
    LEFT JOIN component_arrivals ca ON c.id = ca.component_id
    WHERE 1=1";

$params_avg_price = [];
$types_avg_price = '';

if ($search) {
    $sql_avg_price .= " AND c.name LIKE ?";
    $params_avg_price[] = '%' . $search . '%';
    $types_avg_price .= 's'; // Строковый тип для параметра поиска
}

if ($category) {
    $sql_avg_price .= " AND c.category = ?";
    $params_avg_price[] = $category;
    $types_avg_price .= 's'; // Строковый тип для параметра категории
}

$sql_avg_price .= " GROUP BY c.id";

$stmt_avg_price = $conn->prepare($sql_avg_price);
if ($stmt_avg_price === false) {
    die("Error preparing average price statement: " . $conn->error);
}

// Связывание параметров для среднего запроса
if ($params_avg_price) {
    $stmt_avg_price->bind_param($types_avg_price, ...$params_avg_price);
}

$stmt_avg_price->execute();
$result_avg_price = $stmt_avg_price->get_result();
if ($result_avg_price === false) {
    die("Error executing average price statement: " . $stmt_avg_price->error);
}

// Связывание параметров для среднего запроса
if ($params_avg_price) {
    $stmt_avg_price->bind_param($types_avg_price, ...$params_avg_price);
}

$stmt_avg_price->execute();
$result_avg_price = $stmt_avg_price->get_result();
if ($result_avg_price === false) {
    die("Error executing average price statement: " . $stmt_avg_price->error);
}

// Получение всех уникальных категорий
$categories_sql = "SELECT DISTINCT category FROM components";
$categories_result = $conn->query($categories_sql);
if ($categories_result === false) {
    die("Error fetching categories: " . $conn->error);
}

$conn->close();
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
        .filters {
            margin-bottom: 20px;
        }
        .filters form {
            display: inline;
            margin-right: 10px;
        }
        .filters input, .filters select {
            padding: 5px;
            margin-right: 5px;
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
        .photo {
            width: 100px;
            height: auto;
        }
        .back-button {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #f4f4f4;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 0.5em 1em;
            color: #007BFF;
            text-decoration: none;
            font-weight: bold;
        }
        .back-button:hover {
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="container">
    <a href="index.html" class="back-button">Back to Index</a>

        <h1>Components List</h1>
        <div class="filters">
            <form action="components.php" method="get">
                <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                <select id="category" name="category">
                    <option value="">All Categories</option>
                    <?php
                    if ($categories_result->num_rows > 0) {
                        while ($row = $categories_result->fetch_assoc()) {
                            $cat = htmlspecialchars($row['category']);
                            $selected = ($category == $cat) ? 'selected' : '';
                            echo "<option value='$cat' $selected>$cat</option>";
                        }
                    }
                    ?>
                </select>
                <button type="submit">Apply</button>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Photo</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_avg_price->num_rows > 0) {
                    while ($row = $result_avg_price->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>$" . htmlspecialchars(number_format($row['average_price'], 2)) . "</td>";
                        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                        echo "<td><img src='" . htmlspecialchars($row['photo']) . "' alt='Component Photo' class='photo'></td>";
                        echo "<td>" . htmlspecialchars($row['initial_quantity']) . "</td>";
                        echo "<td>
                            <a class='btn btn-edit' href='edit_component.php?id=" . htmlspecialchars($row['id']) . "'>Edit</a>
                            <a class='btn btn-delete' href='components.php?delete_id=" . htmlspecialchars($row['id']) . "' onclick='return confirm(\"Are you sure you want to delete this component?\")'>Delete</a>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No components found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>